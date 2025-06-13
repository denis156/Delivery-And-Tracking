<?php

namespace App\Livewire\LandingPage;

use Mary\Traits\Toast;
use Livewire\Component;
use Livewire\Attributes\Layout;

 #[Layout('livewire.layouts.landingpage')]
class Index extends Component
{
    use Toast;

    // Contact form properties
    public string $name = '';
    public string $email = '';
    public string $company = '';
    public string $phone = '';
    public string $service_type = '';
    public string $message = '';

    protected $rules = [
        'name' => 'required|string|min:2|max:100',
        'email' => 'required|email|max:100',
        'company' => 'nullable|string|max:100',
        'phone' => 'nullable|string|max:20',
        'service_type' => 'required|string',
        'message' => 'required|string|min:10|max:1000',
    ];

    protected $messages = [
        'name.required' => 'Nama lengkap wajib diisi.',
        'name.min' => 'Nama lengkap minimal 2 karakter.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'service_type.required' => 'Pilih jenis layanan yang diminati.',
        'message.required' => 'Pesan wajib diisi.',
        'message.min' => 'Pesan minimal 10 karakter.',
        'message.max' => 'Pesan maksimal 1000 karakter.',
    ];

    public function sendMessage()
    {
        $this->validate();

        try {
            // In a real application, you would:
            // 1. Save to database
            // 2. Send email notification
            // 3. Send auto-reply to user
            // 4. Log the inquiry

            // For demo purposes, we'll just simulate success
            $this->simulateMessageSending();

            // Reset form
            $this->reset(['name', 'email', 'company', 'phone', 'service_type', 'message']);

            $this->success(
                title: 'Pesan Terkirim!',
                description: 'Tim kami akan menghubungi Anda dalam 24 jam. Terima kasih atas minat Anda pada sistem kami.',
                position: 'toast-top toast-end',
                timeout: 5000
            );

        } catch (\Exception $e) {
            $this->error(
                title: 'Gagal Mengirim Pesan',
                description: 'Terjadi kesalahan saat mengirim pesan. Silakan coba lagi atau hubungi kami langsung.',
                position: 'toast-top toast-end',
                timeout: 5000
            );
        }
    }

    private function simulateMessageSending()
    {
        // Simulate processing time
        sleep(1);

        // In real implementation, this would be:
        /*
        // Save to database
        ContactInquiry::create([
            'name' => $this->name,
            'email' => $this->email,
            'company' => $this->company,
            'phone' => $this->phone,
            'service_type' => $this->service_type,
            'message' => $this->message,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Send notification email to admin
        Notification::route('mail', config('landingpage.contact.email.info'))
            ->notify(new ContactInquiryNotification([
                'name' => $this->name,
                'email' => $this->email,
                'company' => $this->company,
                'phone' => $this->phone,
                'service_type' => $this->service_type,
                'message' => $this->message,
            ]));

        // Send auto-reply to user
        Mail::to($this->email)->send(new ContactAutoReply($this->name));
        */
    }

    public function getServiceTypeOptions()
    {
        return [
            ['id' => 'implementation', 'name' => 'Implementasi Sistem Baru'],
            ['id' => 'consultation', 'name' => 'Konsultasi & Demo'],
            ['id' => 'integration', 'name' => 'Integrasi Sistem Existing'],
            ['id' => 'support', 'name' => 'Support & Maintenance'],
            ['id' => 'other', 'name' => 'Lainnya']
        ];
    }

    public function mount()
    {
        // Set any initial data or perform setup
        // You could also track page views here
        /*
        PageView::create([
            'page' => 'landing-page',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->header('referer'),
        ]);
        */
    }

    public function render()
    {
        return view('livewire.landing-page.index');
    }
}
