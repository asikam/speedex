## Speedex courier Laravel package for SOAP API

A PHP Laravel package for Speedex SOAP API.
   
## Installation

        composer require asikam/speedex

- Then publish the config file

        php artisan vendor:publish --provider="Asikam\Speedex\SpeedexServiceProvider"

## Available Features

- Create Voucher
- Cancel Voucher
- Get Voucher 
- Get Voucher delivery Status (Trace)
- Get vouchers by date (GetConsignmentsByDate)


## Usage

- Create Voucher 

Creating a new voucher and save it as PDF

```php
        use Asikam\Speedex\Speedex;
        
        $speedex = new Speedex();

        $voucher['BOL'][] = [
            '_cust_Flag'          => 0,
            'Items'               => 1,
            'Paratiriseis_2853_1' => "comments",
            'Paratiriseis_2853_2' => 'comments line 2',
            'Paratiriseis_2853_3' => 'comments line 3',
            'PayCode_Flag'        => 1,
            'Pod_Amount_Cash'     => 0,
            // 'Pod_Amount_Description' => 'M',
            'RCV_Addr1'           => "street 52 City",
            'RCV_Country'         => 'Country',
            'RCV_Name'            => "Firstname Surname",
            'RCV_Tel1'            => "0000000000",
            'RCV_Zip_Code'        => "00000",
            'Saturday_Delivery'   => 0,
            'Security_Value'      => 0,
            'Snd_agreement_id'    => $speedex->agreement_id,
            'SND_Customer_Id'     => $speedex->customer_id,
            'Voucher_Weight'      => 1,

        ];

        $speedex->create($voucher);

        if ($speedex->created()) {

            $pdf = $speedex->getPdf();

            Storage::disk('speedex')->put($speedex->getCreatedVoucherCode() . '.pdf',
                $pdf );
        }

```

- Cancel Voucher 


```php
    use Asikam\Speedex\Speedex;
    
    $speedex = new Speedex();

    $response = $speedex->cancel("voucher number");
```

- Trace Voucher 

```php
    use Asikam\Speedex\Speedex;

    $speedex = new Speedex();
    
    $response = $speedex->trace("voucher number");

```

