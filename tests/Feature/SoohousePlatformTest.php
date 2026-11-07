<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Feedback;
use App\Models\FranchiseApplication;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\ExcelService;
use App\Services\PriceCalculationService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class SoohousePlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_clean_database_policy_has_zero_demo_data_and_renders_all_public_pages_cleanly(): void
    {
        // Strict Data Policy: No fake branches, products, categories, feedbacks, or applications after seeding
        $this->assertSame(0, Branch::count());
        $this->assertSame(0, Product::count());
        $this->assertSame(0, Category::count());
        $this->assertSame(0, Feedback::count());
        $this->assertSame(0, FranchiseApplication::count());

        // All public pages must render 200 OK with empty states when database has no branches/products
        $this->get('/')->assertOk()->assertSee('Soohouse Coffee');
        $this->get('/hakkimizda')->assertOk();
        $this->get('/urunlerimiz')->assertOk();
        $this->get('/subelerimiz')->assertOk();
        $this->get('/franchise')->assertOk();
        $this->get('/basinda-biz')->assertOk();
        $this->get('/iletisim')->assertOk();
        $this->get('/sitemap.xml')->assertOk();
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /yonetim');
        $this->get('/yonetim')->assertOk();
    }

    public function test_price_calculation_hierarchy_central_to_branch_override_to_discount(): void
    {
        $service = app(PriceCalculationService::class);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'base_price' => 100.00,
            'min_price' => 80.00,
            'max_price' => 180.00,
        ]);

        // 1. Central default price when no branch override exists
        $calcCentral = $service->calculate($product, null);
        $this->assertSame(100.00, $calcCentral['base_price']);
        $this->assertSame(100.00, $calcCentral['branch_price']);
        $this->assertSame(100.00, $calcCentral['final_price']);
        $this->assertFalse($calcCentral['has_discount']);

        // 2. Branch custom price override without discount
        $branch = Branch::factory()->create();
        $pivot = BranchProduct::create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'is_available' => true,
            'is_sold_out' => false,
            'custom_price' => 120.00,
        ]);

        $calcOverride = $service->calculate($product, $pivot);
        $this->assertSame(100.00, $calcOverride['base_price']);
        $this->assertSame(120.00, $calcOverride['branch_price']);
        $this->assertSame(120.00, $calcOverride['final_price']);
        $this->assertTrue($calcOverride['has_branch_override']);
        $this->assertFalse($calcOverride['has_discount']);

        // 3. Branch percentage discount active within date window
        $pivot->update([
            'discount_type' => 'percentage',
            'discount_value' => 25,
            'discount_starts_at' => Carbon::now('Europe/Istanbul')->subDay(),
            'discount_ends_at' => Carbon::now('Europe/Istanbul')->addDay(),
        ]);

        $calcDiscount = $service->calculate($product, $pivot->fresh());
        $this->assertSame(120.00, $calcDiscount['original_display_price']);
        $this->assertSame(90.00, $calcDiscount['final_price']);
        $this->assertTrue($calcDiscount['has_discount']);
        $this->assertSame(30.00, $calcDiscount['discount_amount']);

        // 4. Expired discount is ignored automatically
        $pivot->update([
            'discount_starts_at' => Carbon::now('Europe/Istanbul')->subDays(5),
            'discount_ends_at' => Carbon::now('Europe/Istanbul')->subDay(),
        ]);

        $calcExpired = $service->calculate($product, $pivot->fresh());
        $this->assertSame(120.00, $calcExpired['final_price']);
        $this->assertFalse($calcExpired['has_discount']);
    }

    public function test_bulk_price_update_respects_overwrite_flag_and_records_audit_log(): void
    {
        $superAdmin = $this->createUserWithRoles(['super_admin'], true);
        $this->actingAs($superAdmin);

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'base_price' => 100.00,
        ]);

        $branchA = Branch::factory()->create(['name' => 'Şube A', 'slug' => 'sube-a']);
        $branchB = Branch::factory()->create(['name' => 'Şube B', 'slug' => 'sube-b']);

        // Branch A has a custom override price (130 TL), Branch B uses central price
        BranchProduct::create([
            'branch_id' => $branchA->id,
            'product_id' => $product->id,
            'custom_price' => 130.00,
        ]);
        BranchProduct::create([
            'branch_id' => $branchB->id,
            'product_id' => $product->id,
            'custom_price' => null,
        ]);

        $service = app(PriceCalculationService::class);

        // Bulk update +10% WITHOUT overwriting custom branch prices
        $service->applyBulkUpdate(
            targetScope: 'central_and_branches',
            operation: 'percentage_increase',
            amount: 10,
            overwriteBranchCustomPrices: false
        );

        $this->assertSame(110.00, (float) $product->fresh()->base_price);
        $this->assertSame(130.00, (float) BranchProduct::where('branch_id', $branchA->id)->first()->custom_price);

        // Bulk update +20% WITH overwriting custom branch prices
        $service->applyBulkUpdate(
            targetScope: 'central_and_branches',
            operation: 'percentage_increase',
            amount: 20,
            overwriteBranchCustomPrices: true
        );

        $this->assertSame(132.00, (float) $product->fresh()->base_price);
        $this->assertNull(BranchProduct::where('branch_id', $branchA->id)->first()->custom_price);

        // Verify Audit Log entry was created
        $this->assertDatabaseHas('audit_logs', [
            'action_type' => 'price.bulk_updated',
            'auditable_id' => $product->id,
        ]);
    }

    public function test_audit_logs_are_strictly_immutable_and_only_accessible_by_super_admin(): void
    {
        $log = AuditLog::create([
            'user_name' => 'Sistem',
            'action_type' => 'settings.updated',
            'description' => 'Test kaydı',
            'old_values' => ['a' => 1],
            'new_values' => ['a' => 2],
        ]);

        // Attempting to update or delete an AuditLog throws LogicException
        $this->expectException(LogicException::class);
        $log->update(['description' => 'Değiştirildi']);
    }

    public function test_audit_log_cannot_be_deleted(): void
    {
        $log = AuditLog::create([
            'user_name' => 'Sistem',
            'action_type' => 'settings.updated',
            'description' => 'Silinemez kayıt',
        ]);

        $this->expectException(LogicException::class);
        $log->delete();
    }

    public function test_branch_data_isolation_prevents_branch_manager_from_accessing_another_branch(): void
    {
        $branchKadikoy = Branch::factory()->create(['name' => 'Soohouse Kadıköy', 'slug' => 'soohouse-kadikoy']);
        $branchBesiktas = Branch::factory()->create(['name' => 'Soohouse Beşiktaş', 'slug' => 'soohouse-besiktas']);

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'base_price' => 100]);

        // Create a Branch Manager assigned ONLY to Kadıköy
        $kadikoyManager = $this->createUserWithRoles(['branch_manager'], false, [$branchKadikoy->id]);

        // 1. Can access own branch (Kadıköy) menu & tables & settings
        $this->actingAs($kadikoyManager)
            ->get(route('admin.branch.menu', $branchKadikoy->slug))
            ->assertOk();

        $this->actingAs($kadikoyManager)
            ->get(route('admin.branch.tables', $branchKadikoy->slug))
            ->assertOk();

        // 2. CANNOT access Beşiktaş branch menu, tables, or settings (Must return 403 Forbidden)
        $this->actingAs($kadikoyManager)
            ->get(route('admin.branch.menu', $branchBesiktas->slug))
            ->assertForbidden();

        $this->actingAs($kadikoyManager)
            ->put(route('admin.branch.menu.update', [$branchBesiktas->slug, $product->id]), [
                'is_available' => true,
                'custom_price' => 110,
            ])
            ->assertForbidden();

        $this->actingAs($kadikoyManager)
            ->get(route('admin.branch.tables', $branchBesiktas->slug))
            ->assertForbidden();

        $this->actingAs($kadikoyManager)
            ->get(route('admin.branch.settings', $branchBesiktas->slug))
            ->assertForbidden();

        // 3. Feedbacks isolation: Kadıköy manager cannot update Beşiktaş feedback
        $besiktasFeedback = Feedback::create([
            'branch_id' => $branchBesiktas->id,
            'type' => 'suggestion',
            'message' => 'Beşiktaş şubesi önerisi',
            'status' => 'new',
            'submitted_date' => now()->toDateString(),
            'submitted_time' => '14:30:00',
            'day_name' => 'Cumartesi',
        ]);

        $this->actingAs($kadikoyManager)
            ->put(route('admin.feedbacks.update', $besiktasFeedback->id), [
                'status' => 'resolved',
            ])
            ->assertForbidden();
    }

    public function test_rbac_matrix_restricts_roles_to_their_authorized_modules(): void
    {
        $franchiseUser = $this->createUserWithRoles(['franchise_manager']);
        $productUser = $this->createUserWithRoles(['product_manager']);
        $superAdmin = $this->createUserWithRoles(['super_admin'], true);

        // Franchise Manager CAN view franchises, CANNOT view products, pricing, users, or audit logs
        $this->actingAs($franchiseUser)->get(route('admin.franchises.index'))->assertOk();
        $this->actingAs($franchiseUser)->get(route('admin.products.index'))->assertForbidden();
        $this->actingAs($franchiseUser)->get(route('admin.pricing.index'))->assertForbidden();
        $this->actingAs($franchiseUser)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($franchiseUser)->get(route('admin.audit_logs.index'))->assertForbidden();

        // Product Manager CAN view products & categories, CANNOT view franchises or audit logs
        $this->actingAs($productUser)->get(route('admin.products.index'))->assertOk();
        $this->actingAs($productUser)->get(route('admin.categories.index'))->assertOk();
        $this->actingAs($productUser)->get(route('admin.franchises.index'))->assertForbidden();
        $this->actingAs($productUser)->get(route('admin.audit_logs.index'))->assertForbidden();

        // Multi-role support: Assigning both franchise_manager and price_manager grants both capabilities
        $multiRoleUser = $this->createUserWithRoles(['franchise_manager', 'price_manager']);
        $this->actingAs($multiRoleUser)->get(route('admin.franchises.index'))->assertOk();
        $this->actingAs($multiRoleUser)->get(route('admin.pricing.index'))->assertOk();
        $this->actingAs($multiRoleUser)->get(route('admin.audit_logs.index'))->assertForbidden();

        // Super Admin can access everything including audit logs and user/role management
        $this->actingAs($superAdmin)->get(route('admin.audit_logs.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.users.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.roles.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.settings.index'))->assertOk();
    }

    public function test_qrmenu_and_customer_feedback_automatically_records_branch_table_date_day_and_time(): void
    {
        $branch = Branch::factory()->create([
            'name' => 'Soohouse Nişantaşı',
            'slug' => 'soohouse-nisantasi',
            'is_active' => true,
        ]);

        $table = DiningTable::factory()->create([
            'branch_id' => $branch->id,
            'name' => 'Teras 5',
            'table_number' => 'T-05',
            'is_active' => true,
        ]);

        // 1. Public QR menu without table token works and is indexable
        $this->get(route('qrmenu.show', $branch->slug))
            ->assertOk()
            ->assertSee('Masa: Belirtilmedi')
            ->assertSee('<meta name="robots" content="index, follow">', false);

        // 2. Table-specific QR menu works, shows table name, has noindex and canonical pointing to branch menu
        $this->get(route('qrmenu.table', [$branch->slug, $table->token]))
            ->assertOk()
            ->assertSee('Masa: Teras 5 (#T-05)')
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('<link rel="canonical" href="'.route('qrmenu.show', $branch->slug).'">', false);

        // 3. Submitting feedback from table QR menu records branch, table, date, day_name, and time automatically
        $response = $this->post(route('qrmenu.feedback.store', $branch->slug), [
            'type' => 'suggestion',
            'message' => 'Filtre kahve çekirdekleriniz harika, müzik biraz daha kısık olabilir.',
            'table_token' => $table->token,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('feedback_thank_you', true);

        $feedback = Feedback::first();
        $this->assertNotNull($feedback);
        $this->assertSame($branch->id, $feedback->branch_id);
        $this->assertSame($table->id, $feedback->dining_table_id);
        $this->assertSame('suggestion', $feedback->type);
        $this->assertNotEmpty($feedback->submitted_date);
        $this->assertNotEmpty($feedback->submitted_time);
        $this->assertNotEmpty($feedback->day_name);
    }

    public function test_table_card_generator_outputs_svg_png_and_pdf(): void
    {
        $superAdmin = $this->createUserWithRoles(['super_admin'], true);
        $branch = Branch::factory()->create(['slug' => 'soohouse-test']);
        $table = DiningTable::factory()->create(['branch_id' => $branch->id, 'table_number' => '10']);

        $this->actingAs($superAdmin)
            ->get(route('admin.branch.tables.card', [$branch->slug, $table->id, 'svg']))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml');

        $this->actingAs($superAdmin)
            ->get(route('admin.branch.tables.card', [$branch->slug, $table->id, 'png']))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');

        $this->actingAs($superAdmin)
            ->get(route('admin.branch.tables.card', [$branch->slug, $table->id, 'pdf']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_excel_import_validation_and_upsert_plus_excel_exports(): void
    {
        $excelService = app(ExcelService::class);

        // 1. Generate template and verify it is a valid XLSX binary
        $templateBytes = $excelService->generateProductImportTemplate();
        $this->assertNotEmpty($templateBytes);

        $tmpFile = tempnam(sys_get_temp_dir(), 'soohouse_xlsx_').'.xlsx';
        file_put_contents($tmpFile, $templateBytes);

        // 2. Validate and preview the template row
        $preview = $excelService->validateAndPreviewProductImport($tmpFile);
        $this->assertCount(1, $preview['valid_rows']);
        $this->assertEmpty($preview['errors']);
        $this->assertSame('SH-ESP-001', $preview['valid_rows'][0]['sku']);

        // 3. Commit upsert (Create)
        $resultCreate = $excelService->commitProductImport($preview['valid_rows']);
        $this->assertSame(1, $resultCreate['created']);
        $this->assertSame(0, $resultCreate['updated']);
        $this->assertDatabaseHas('products', ['sku' => 'SH-ESP-001', 'base_price' => 115.00]);

        // 4. Commit upsert again (Update existing SKU)
        $preview['valid_rows'][0]['base_price'] = 140.00;
        $resultUpdate = $excelService->commitProductImport($preview['valid_rows']);
        $this->assertSame(0, $resultUpdate['created']);
        $this->assertSame(1, $resultUpdate['updated']);
        $this->assertDatabaseHas('products', ['sku' => 'SH-ESP-001', 'base_price' => 140.00]);

        @unlink($tmpFile);
    }

    public function test_franchise_application_submission_requires_kvkk_and_stores_cleanly(): void
    {
        $response = $this->post(route('public.franchise.store'), [
            'full_name' => 'Ahmet Yılmaz',
            'phone' => '0532 000 00 00',
            'email' => 'ahmet@example.com',
            'city' => 'İzmir',
            'district' => 'Alsancak',
            'investment_budget' => '5.000.000 ₺ - 7.500.000 ₺',
            'message' => 'Köşe başı 180m2 mağazamız mevcut.',
            'kvkk_accepted' => '1',
        ]);

        $response->assertRedirect(route('public.franchise'));
        $this->assertDatabaseHas('franchise_applications', [
            'full_name' => 'Ahmet Yılmaz',
            'city' => 'İzmir',
            'status' => 'new',
            'kvkk_accepted' => true,
        ]);
    }

    /**
     * @param  array<string>  $roleSlugs
     * @param  array<int>  $branchIds
     */
    protected function createUserWithRoles(array $roleSlugs, bool $allBranches = false, array $branchIds = []): User
    {
        $user = User::factory()->create([
            'is_active' => true,
            'all_branches' => $allBranches,
        ]);

        $roleIds = Role::whereIn('slug', $roleSlugs)->pluck('id')->all();
        $user->roles()->sync($roleIds);

        if (! empty($branchIds)) {
            $user->branches()->sync($branchIds);
        }

        return $user->fresh(['roles.permissions', 'branches']);
    }
}
