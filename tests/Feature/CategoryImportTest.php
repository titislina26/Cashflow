<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use Illuminate\Http\UploadedFile;

class CategoryImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_import_categories_from_csv()
    {
        $csvContent = "NP,Account\n";
        $csvContent .= "4-1100,SPP Teknik Elektro\n";
        $csvContent .= "6-1200,Beban ATK\n";

        $file = UploadedFile::fake()->createWithContent('categories.csv', $csvContent);

        $response = $this->post(route('categories.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('categories', [
            'code' => '4-1100',
            'name' => 'SPP Teknik Elektro',
            'type' => 'income',
        ]);

        $this->assertDatabaseHas('categories', [
            'code' => '6-1200',
            'name' => 'Beban ATK',
            'type' => 'expense',
        ]);
    }

    public function test_can_download_template()
    {
        $response = $this->get(route('categories.import-template'));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('Content-Type'), 'text/csv'));
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition'), 'template_kategori.csv'));
    }
}
