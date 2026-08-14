<?php

namespace Tests\Feature;

use App\Enums\Condition;
use App\Enums\EquipmentStatus;
use App\Enums\RentalStatus;
use App\Models\Equipment;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EquipmentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_crud_cycle(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('equipment.index'))->assertOk();
        $this->actingAs($user)->get(route('equipment.create'))->assertOk();

        $store = $this->actingAs($user)->post('/equipment', [
            'name' => 'Canon EOS R5',
            'category' => 'camera',
            'serial_no' => 'CAM-0001',
            'condition' => Condition::Good->value,
            'status' => EquipmentStatus::Available->value,
        ]);
        $store->assertRedirect(route('equipment.index'));
        $store->assertSessionHas('status', 'equipment-created');

        $equipment = Equipment::where('serial_no', 'CAM-0001')->firstOrFail();

        $this->actingAs($user)->get(route('equipment.show', $equipment))->assertOk();
        $this->actingAs($user)->get(route('equipment.edit', $equipment))->assertOk();

        $update = $this->actingAs($user)->put(route('equipment.update', $equipment), [
            'name' => 'Canon EOS R5 Mark II',
            'category' => 'camera',
            'serial_no' => 'CAM-0001',
            'condition' => Condition::Good->value,
            'status' => EquipmentStatus::Available->value,
        ]);
        $update->assertRedirect(route('equipment.index'));
        $update->assertSessionHas('status', 'equipment-updated');
        $this->assertSame('Canon EOS R5 Mark II', $equipment->refresh()->name);

        $destroy = $this->actingAs($user)->delete(route('equipment.destroy', $equipment));
        $destroy->assertRedirect(route('equipment.index'));
        $destroy->assertSessionHas('status', 'equipment-deleted');
        $this->assertNull($equipment->fresh());
    }

    public function test_serial_collision_is_rejected(): void
    {
        $user = User::factory()->create();
        Equipment::factory()->create(['serial_no' => 'CAM-0009']);

        $response = $this->actingAs($user)->post('/equipment', [
            'name' => 'Canon EOS R5',
            'category' => 'camera',
            'serial_no' => 'CAM-0009',
            'condition' => Condition::Good->value,
            'status' => EquipmentStatus::Available->value,
        ]);

        $response->assertSessionHasErrors('serial_no');
    }

    public function test_index_filters_to_available_equipment(): void
    {
        $user = User::factory()->create();
        $available = Equipment::factory()->create(['status' => EquipmentStatus::Available]);
        $checkedOut = Equipment::factory()->create(['status' => EquipmentStatus::CheckedOut]);

        $response = $this->actingAs($user)->get(route('equipment.index', ['status' => 'available']));

        $response->assertOk();
        $response->assertSeeText($available->serial_no);
        $response->assertDontSeeText($checkedOut->serial_no);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('equipment.index'))->assertRedirect(route('login'));
    }

    public function test_photo_upload_stores_file_and_saves_path(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/equipment', [
            'name' => 'Shure SM7B',
            'category' => 'av',
            'serial_no' => 'AV-0001',
            'condition' => Condition::Good->value,
            'status' => EquipmentStatus::Available->value,
            'photo' => UploadedFile::fake()->image('mic.jpg'),
        ]);

        $response->assertRedirect(route('equipment.index'));

        $equipment = Equipment::where('serial_no', 'AV-0001')->firstOrFail();
        $this->assertNotNull($equipment->photo);
        Storage::disk('public')->assertExists($equipment->photo);
    }

    public function test_updating_photo_removes_the_old_file(): void
    {
        Storage::fake('public');
        $oldPath = Storage::disk('public')->put('equipment', UploadedFile::fake()->image('old.jpg'));
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['photo' => $oldPath]);

        $response = $this->actingAs($user)->put(route('equipment.update', $equipment), [
            'name' => $equipment->name,
            'category' => $equipment->category,
            'serial_no' => $equipment->serial_no,
            'condition' => $equipment->condition->value,
            'status' => $equipment->status->value,
            'photo' => UploadedFile::fake()->image('new.jpg'),
        ]);

        $response->assertRedirect(route('equipment.index'));
        $equipment->refresh();
        $this->assertNotSame($oldPath, $equipment->photo);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($equipment->photo);
    }

    public function test_duplicate_creates_a_second_row_with_a_different_serial(): void
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create([
            'serial_no' => 'CAM-0002',
            'photo' => 'equipment/original.jpg',
            'status' => EquipmentStatus::CheckedOut,
        ]);

        $response = $this->actingAs($user)->post(route('equipment.duplicate', $equipment));

        $response->assertRedirect(route('equipment.index'));
        $response->assertSessionHas('status', 'equipment-duplicated');
        $this->assertSame(2, Equipment::where('name', $equipment->name)->count());

        $copy = Equipment::where('name', $equipment->name)->where('serial_no', '!=', 'CAM-0002')->firstOrFail();
        $this->assertNull($copy->photo);
        $this->assertSame(EquipmentStatus::Available, $copy->status);
    }

    public function test_deleting_equipment_removes_its_photo_file(): void
    {
        Storage::fake('public');
        $path = Storage::disk('public')->put('equipment', UploadedFile::fake()->image('drill.jpg'));
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create(['photo' => $path]);

        $this->actingAs($user)->delete(route('equipment.destroy', $equipment));

        Storage::disk('public')->assertMissing($path);
    }

    public function test_deleting_equipment_with_an_active_rental_is_blocked(): void
    {
        $user = User::factory()->create();
        $equipment = Equipment::factory()->create();
        Rental::factory()->create(['equipment_id' => $equipment->id, 'status' => RentalStatus::Active]);

        $response = $this->actingAs($user)->delete(route('equipment.destroy', $equipment));

        $response->assertRedirect(route('equipment.index'));
        $response->assertSessionHas('error');
        $this->assertNotNull($equipment->fresh());
    }
}
