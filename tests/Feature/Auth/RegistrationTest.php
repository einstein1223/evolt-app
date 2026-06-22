<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

   public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'username' => 'testuser123',
            'nomor_telepon' => '081234567890',
            'email' => 'test@example.com',
            // Gunakan kata sandi yang kuat di sini:
            'password' => 'PasswordKuat123!',
            'password_confirmation' => 'PasswordKuat123!',
        ]);

       $this->assertGuest();
        
        // 3. Ubah ini: Pastikan sistem mengarahkan user ke halaman login (sesuaikan rutenya jika berbeda)
        $response->assertRedirect('/login'); 
        
        // (Opsional) Memastikan session membawa pesan sukses Anda
        $response->assertSessionHas('message', 'Registrasi Berhasil! Silakan Login.');
    
    }
}
