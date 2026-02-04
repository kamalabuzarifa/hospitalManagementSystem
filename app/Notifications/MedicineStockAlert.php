<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class MedicineStockAlert extends Notification
{
    use Queueable;

    public string $type; // 'low' or 'out'
    public int $medicineId;
    public string $medicineName;
    public int $quantity;

    public function __construct(string $type, int $medicineId, string $medicineName, int $quantity)
    {
        $this->type = $type;
        $this->medicineId = $medicineId;
        $this->medicineName = $medicineName;
        $this->quantity = $quantity;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $isLow = $this->type === 'low';
        $messageEn = $isLow
            ? "Low stock: {$this->medicineName} (remaining: {$this->quantity})"
            : "Out of stock: {$this->medicineName}";
        $messageAr = $isLow
            ? "كمية منخفضة: {$this->medicineName} (المتبقي: {$this->quantity})"
            : "نفدت الكمية: {$this->medicineName}";

        return [
            'type' => 'medicine_stock',
            'subtype' => $this->type,
            'medicine_id' => $this->medicineId,
            'medicine_name' => $this->medicineName,
            'quantity' => $this->quantity,
            'message_en' => $messageEn,
            'message_ar' => $messageAr,
        ];
    }
}
