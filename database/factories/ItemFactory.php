<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\DeliveryOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Elektronik' => [
                'items' => [
                    'Laptop Dell Inspiron 15',
                    'Mouse Wireless Logitech',
                    'Keyboard Mechanical RGB',
                    'Monitor LED 24 inch',
                    'Smartphone Samsung Galaxy',
                    'Tablet iPad Pro',
                    'Headphone Sony WH-1000XM4',
                    'Speaker Bluetooth JBL',
                    'Webcam Logitech C920',
                    'Power Bank Xiaomi 20000mAh',
                ],
                'units' => ['pcs', 'unit', 'set'],
                'weight_range' => [0.1, 5.0],
                'value_range' => [100000, 15000000],
            ],
            'Furniture' => [
                'items' => [
                    'Meja Kantor Minimalis',
                    'Kursi Ergonomis Executive',
                    'Lemari Pakaian 3 Pintu',
                    'Sofa 3 Dudukan',
                    'Tempat Tidur Queen Size',
                    'Rak Buku 5 Tingkat',
                    'Meja Makan Set 6 Kursi',
                    'Cermin Dinding Besar',
                    'Lemari TV Modern',
                    'Nakas Samping Tempat Tidur',
                ],
                'units' => ['pcs', 'set', 'unit'],
                'weight_range' => [5.0, 150.0],
                'value_range' => [500000, 25000000],
            ],
            'Tekstil' => [
                'items' => [
                    'Kain Batik Premium Meter',
                    'Baju Kemeja Pria',
                    'Dress Wanita Casual',
                    'Celana Jeans Original',
                    'Jaket Kulit Asli',
                    'Sepatu Formal Pria',
                    'Tas Handbag Wanita',
                    'Hijab Segi Empat',
                    'Kaos Polo Shirt',
                    'Topi Baseball Cap',
                ],
                'units' => ['pcs', 'meter', 'lusin', 'pasang'],
                'weight_range' => [0.1, 2.0],
                'value_range' => [50000, 2000000],
            ],
            'Makanan' => [
                'items' => [
                    'Beras Premium 25kg',
                    'Minyak Goreng Kemasan',
                    'Gula Pasir Kristal',
                    'Tepung Terigu Segitiga',
                    'Kopi Arabica Toraja',
                    'Teh Celup Premium',
                    'Snack Kemasan Dus',
                    'Mie Instan Karton',
                    'Susu UHT Kotak',
                    'Biskuit Kaleng Import',
                ],
                'units' => ['kg', 'liter', 'dus', 'karton', 'sak'],
                'weight_range' => [0.5, 50.0],
                'value_range' => [25000, 1000000],
                'special' => ['requires_cold_storage' => 0.2], // 20% chance
            ],
            'Alat Tulis' => [
                'items' => [
                    'Kertas HVS A4 Rim',
                    'Pulpen Pilot G2',
                    'Pensil 2B Faber Castell',
                    'Penghapus Karet',
                    'Spidol Whiteboard',
                    'Stapler Joyko Heavy Duty',
                    'Map Plastik Transparan',
                    'Buku Tulis 58 Lembar',
                    'Kalkulator Casio Scientific',
                    'Penggaris Besi 30cm',
                ],
                'units' => ['rim', 'pcs', 'lusin', 'pack', 'box'],
                'weight_range' => [0.1, 10.0],
                'value_range' => [5000, 500000],
            ],
            'Otomotif' => [
                'items' => [
                    'Ban Mobil Bridgestone',
                    'Oli Mesin Castrol 4L',
                    'Aki Mobil GS Astra',
                    'Filter Udara Original',
                    'Spare Part Mesin',
                    'Velg Racing 17 inch',
                    'Lampu LED Philips',
                    'Kaca Spion Honda',
                    'Kampas Rem Toyota',
                    'Radiator Suzuki',
                ],
                'units' => ['pcs', 'liter', 'set', 'unit'],
                'weight_range' => [0.5, 25.0],
                'value_range' => [50000, 5000000],
            ],
        ];

        $category = fake()->randomElement(array_keys($categories));
        $categoryData = $categories[$category];

        $itemName = fake()->randomElement($categoryData['items']);
        $unit = fake()->randomElement($categoryData['units']);
        $weight = fake()->randomFloat(2, $categoryData['weight_range'][0], $categoryData['weight_range'][1]);
        $plannedQuantity = fake()->randomFloat(2, 1, 50);
        $unitValue = fake()->randomFloat(2, $categoryData['value_range'][0], $categoryData['value_range'][1]);
        $totalValue = $unitValue * $plannedQuantity;

        // Determine special handling requirements
        $isFragile = in_array($category, ['Elektronik']) ? fake()->boolean(60) : fake()->boolean(5);
        $requiresColdStorage = isset($categoryData['special']['requires_cold_storage'])
            ? fake()->boolean($categoryData['special']['requires_cold_storage'] * 100)
            : false;

        return [
            'delivery_order_id' => DeliveryOrder::factory(),
            'name' => $itemName,
            'description' => fake()->optional(0.7)->sentence(),
            'category' => $category,
            'unit' => $unit,
            'planned_quantity' => $plannedQuantity,
            'actual_quantity' => null,
            'weight' => $weight,
            'length' => fake()->optional(0.6)->randomFloat(2, 10, 200), // cm
            'width' => fake()->optional(0.6)->randomFloat(2, 10, 150),  // cm
            'height' => fake()->optional(0.6)->randomFloat(2, 5, 100),  // cm
            'unit_value' => $unitValue,
            'total_value' => $totalValue,
            'is_insured' => $totalValue > 1000000 ? fake()->boolean(80) : fake()->boolean(20),
            'condition_sent' => Item::CONDITION_GOOD,
            'condition_received' => null,
            'is_fragile' => $isFragile,
            'requires_cold_storage' => $requiresColdStorage,
            'status' => Item::STATUS_PREPARED,
            'notes' => fake()->optional(0.3)->sentence(),
            'damage_notes' => null,
            'barcode' => fake()->optional(0.5)->ean13(),
            'serial_number' => fake()->optional(0.3)->regexify('[A-Z]{2}[0-9]{8}'),
            'sort_order' => 0, // Will be auto-generated in model
        ];
    }

    /**
     * Electronics category items
     */
    public function electronics(): static
    {
        return $this->state(function (array $attributes) {
            $electronics = [
                'Laptop ASUS ROG Gaming',
                'Monitor Samsung Curved 27"',
                'Printer Canon PIXMA',
                'Hard Drive External 2TB',
                'RAM DDR4 16GB',
                'SSD Samsung 1TB',
                'Graphics Card RTX 4060',
                'Motherboard MSI Gaming',
                'CPU Intel Core i7',
                'Cooling Fan RGB',
            ];

            return [
                'name' => fake()->randomElement($electronics),
                'category' => 'Elektronik',
                'unit' => 'pcs',
                'is_fragile' => true,
                'is_insured' => fake()->boolean(90),
                'unit_value' => fake()->randomFloat(2, 500000, 25000000),
                'weight' => fake()->randomFloat(2, 0.5, 10.0),
                'barcode' => fake()->ean13(),
                'serial_number' => fake()->regexify('[A-Z]{2}[0-9]{8}'),
            ];
        });
    }

    /**
     * Food category items
     */
    public function food(): static
    {
        return $this->state(function (array $attributes) {
            $foods = [
                'Daging Sapi Segar 1kg',
                'Ikan Salmon Import',
                'Sayuran Organik Mix',
                'Buah Apel Fuji',
                'Susu Segar Pasteurisasi',
                'Yogurt Probiotik',
                'Es Krim Premium',
                'Frozen Food Siap Saji',
                'Keju Cheddar Import',
                'Daging Ayam Fillet',
            ];

            return [
                'name' => fake()->randomElement($foods),
                'category' => 'Makanan',
                'unit' => fake()->randomElement(['kg', 'liter', 'pack', 'dus']),
                'requires_cold_storage' => true,
                'unit_value' => fake()->randomFloat(2, 25000, 500000),
                'weight' => fake()->randomFloat(2, 0.5, 25.0),
            ];
        });
    }

    /**
     * Furniture category items
     */
    public function furniture(): static
    {
        return $this->state(function (array $attributes) {
            $furniture = [
                'Meja Kantor Standing Desk',
                'Kursi Gaming RGB',
                'Lemari Sliding Door',
                'Sofa Bed Multifungsi',
                'Tempat Tidur Minimalis',
                'Rak Buku Industrial',
                'Meja Makan Kayu Jati',
                'Cermin LED Smart',
                'Cabinet TV Modern',
                'Ottoman Storage Box',
            ];

            return [
                'name' => fake()->randomElement($furniture),
                'category' => 'Furniture',
                'unit' => fake()->randomElement(['pcs', 'set']),
                'unit_value' => fake()->randomFloat(2, 500000, 15000000),
                'weight' => fake()->randomFloat(2, 10.0, 100.0),
                'length' => fake()->randomFloat(2, 50, 200),
                'width' => fake()->randomFloat(2, 40, 150),
                'height' => fake()->randomFloat(2, 30, 120),
            ];
        });
    }

    /**
     * Automotive parts
     */
    public function automotive(): static
    {
        return $this->state(function (array $attributes) {
            $automotive = [
                'Ban Mobil Michelin 185/65R15',
                'Oli Mesin Shell Helix 5W-30',
                'Aki Mobil Yuasa 65Ah',
                'Filter Udara K&N Performance',
                'Shock Absorber Bilstein',
                'Velg Racing Enkei 17x7',
                'Lampu HID Xenon 6000K',
                'Kaca Spion Elektrik',
                'Kampas Rem Brembo',
                'Radiator Denso Original',
            ];

            return [
                'name' => fake()->randomElement($automotive),
                'category' => 'Otomotif',
                'unit' => fake()->randomElement(['pcs', 'liter', 'set']),
                'unit_value' => fake()->randomFloat(2, 100000, 3000000),
                'weight' => fake()->randomFloat(2, 1.0, 20.0),
                'barcode' => fake()->ean13(),
            ];
        });
    }

    /**
     * Fragile items
     */
    public function fragile(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_fragile' => true,
            'is_insured' => fake()->boolean(80),
            'notes' => 'FRAGILE - Handle with care',
        ]);
    }

    /**
     * High value items
     */
    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'unit_value' => fake()->randomFloat(2, 5000000, 50000000),
            'is_insured' => true,
            'is_fragile' => fake()->boolean(70),
            'notes' => 'High value item - extra security required',
        ]);
    }

    /**
     * Items with serial numbers
     */
    public function withSerial(): static
    {
        return $this->state(fn (array $attributes) => [
            'serial_number' => fake()->regexify('[A-Z]{2}[0-9]{8}'),
            'barcode' => fake()->ean13(),
        ]);
    }

    /**
     * Delivered items
     */
    public function delivered(): static
    {
        return $this->state(function (array $attributes) {
            $variance = fake()->randomFloat(2, 0.95, 1.05); // ±5% variance
            $actualQuantity = $attributes['planned_quantity'] * $variance;

            return [
                'status' => Item::STATUS_DELIVERED,
                'actual_quantity' => $actualQuantity,
                'condition_received' => fake()->randomElement([
                    Item::CONDITION_GOOD,
                    Item::CONDITION_GOOD,
                    Item::CONDITION_GOOD, // Higher chance of good condition
                    Item::CONDITION_MINOR_DAMAGE,
                ]),
            ];
        });
    }

    /**
     * Damaged items
     */
    public function damaged(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Item::STATUS_DAMAGED,
            'condition_received' => fake()->randomElement([
                Item::CONDITION_MINOR_DAMAGE,
                Item::CONDITION_MAJOR_DAMAGE,
            ]),
            'damage_notes' => fake()->randomElement([
                'Kemasan rusak saat pengiriman',
                'Barang retak pada bagian sudut',
                'Terkena air hujan selama perjalanan',
                'Jatuh saat proses pembongkaran',
                'Kemasan terbuka sebagian',
                'Goresan pada permukaan',
                'Penyok akibat benturan',
                'Cacat produksi terdeteksi',
            ]),
        ]);
    }

    /**
     * Items with discrepancy
     */
    public function withDiscrepancy(): static
    {
        return $this->state(function (array $attributes) {
            $variance = fake()->randomElement([-0.3, -0.2, -0.1, 0.1, 0.2]); // Missing or extra items
            $actualQuantity = max(0, $attributes['planned_quantity'] * (1 + $variance));

            return [
                'actual_quantity' => $actualQuantity,
                'condition_received' => fake()->randomElement([
                    Item::CONDITION_GOOD,
                    Item::CONDITION_MINOR_DAMAGE,
                ]),
                'notes' => $actualQuantity < $attributes['planned_quantity']
                    ? 'Kekurangan quantity dari rencana'
                    : 'Kelebihan quantity dari rencana',
            ];
        });
    }

    /**
     * Items requiring cold storage
     */
    public function coldStorage(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_cold_storage' => true,
            'category' => 'Makanan',
            'notes' => 'Requires temperature-controlled storage',
        ]);
    }

    /**
     * Bulk quantity items
     */
    public function bulk(): static
    {
        return $this->state(fn (array $attributes) => [
            'planned_quantity' => fake()->randomFloat(2, 100, 1000),
            'unit' => fake()->randomElement(['kg', 'liter', 'karton', 'dus']),
            'weight' => fake()->randomFloat(2, 50, 500),
        ]);
    }

    /**
     * Small package items
     */
    public function smallPackage(): static
    {
        return $this->state(fn (array $attributes) => [
            'planned_quantity' => fake()->randomFloat(2, 1, 5),
            'weight' => fake()->randomFloat(2, 0.1, 2.0),
            'length' => fake()->randomFloat(2, 5, 30),
            'width' => fake()->randomFloat(2, 5, 25),
            'height' => fake()->randomFloat(2, 3, 20),
        ]);
    }

    /**
     * Office supplies
     */
    public function officeSupplies(): static
    {
        return $this->state(function (array $attributes) {
            $supplies = [
                'Kertas HVS A4 80gsm',
                'Tinta Printer Canon Original',
                'Stapler Heavy Duty Kenko',
                'Map File Plastik L',
                'Ballpoint Pilot G2 0.7mm',
                'Post-it Notes 3x3 inch',
                'Penghapus Faber Castell',
                'Correction Tape Kenko',
                'Cutter Kenko L-150',
                'Double Tape 3M 24mm',
            ];

            return [
                'name' => fake()->randomElement($supplies),
                'category' => 'Alat Tulis',
                'unit' => fake()->randomElement(['rim', 'pcs', 'pack', 'box', 'lusin']),
                'unit_value' => fake()->randomFloat(2, 5000, 200000),
                'weight' => fake()->randomFloat(2, 0.1, 5.0),
            ];
        });
    }
}
