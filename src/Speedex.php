<?php

namespace Asikam\Speedex;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use SoapFault;

class Speedex
{
    /**
     * Company login credentials
     *
     * @var string
     */
    private string $name;

    /**
     * @var string|null
     */
    private ?string $username;

    /**
     * @var string|null
     */
    private ?string $password;

    /**
     * @var string|null
     */
    public ?string $customer_id;

    /**
     * @var string|null
     */
    public ?string $agreement_id;


    /**
     * Request context
     * @var mixed
     */
    protected mixed $context;

    /**
     * Request wsdlUrl
     *
     * @var string
     */
    protected string $wsdlUrl;

    /**
     * Request client
     *
     * @var mixed
     */
    protected mixed $client;

    /**
     * Request parameters
     *
     * @var mixed
     */
    private mixed $parameters;
    /**
     * Request session_id
     *
     * @var string
     */
    public string $session_id;

    /**
     * @var string|null
     */
    public ?string $environment;

    /**
     * @var mixed
     */
    public mixed $created ;

    /**
     * @throws SoapFault
     * @throws Exception
     */
    public function __construct()
    {
        $this->setEnv();
    }

    /**
     * @throws SoapFault
     * @throws Exception
     */
    public function setEnv($environment = null ): void
    {
        $this->environment = $environment ?? App::environment();

        switch ($this->environment) {
            case 'local':
                $this->wsdlUrl      = config('speedex.SPEEDEX_DEV_URL');
                $this->name         = config('speedex.SPEEDEX_DEV_NAME');
                $this->username     = config('speedex.SPEEDEX_DEV_USERNAME');
                $this->password     = config('speedex.SPEEDEX_DEV_PASSWORD');
                $this->customer_id  = config('speedex.SPEEDEX_DEV_SND_CUSTOMER_ID');
                $this->agreement_id = config('speedex.SPEEDEX_DEV_SND_AGREEMENT_ID');

                break;
            default:

                $this->wsdlUrl      = config('speedex.SPEEDEX_URL');
                $this->name         = config('speedex.SPEEDEX_NAME');
                $this->username     = config('speedex.SPEEDEX_USERNAME');
                $this->password     = config('speedex.SPEEDEX_PASSWORD');
                $this->customer_id  = config('speedex.SPEEDEX_SND_CUSTOMER_ID');
                $this->agreement_id = config('speedex.SPEEDEX_SND_AGREEMENT_ID');

                break;
        }

        $this->setOptions();
        $this->session_id();

        if(!isset($this->session_id)){
            throw new Exception('Speedex session id not set Credential error');
        }

        $this->username    = null;
        $this->password    = null;
        $this->parameters  = [];

    }


    /**
     * @throws SoapFault
     */
    public function setOptions(array $options = null ): void
    {
        $options = $options ?? array(
            'http' => array(
                'user_agent' => 'PHPSoapClient'
            )
        );

        $this->context = stream_context_create($options);

        $soapClientOptions = array(
            'stream_context' => $this->context,
            'cache_wsdl' => 'WSDL_CACHE_NONE'
        );

        $this->client = new \SoapClient($this->wsdlUrl, $soapClientOptions);

    }

    /**
     * @throws Exception
     */
    public function session_id(): string
    {

        try {

            $this->parameters = array(
                'username' => $this->username,
                'password' => $this->password
            );

            $result = $this->client->CreateSession($this->parameters);

            $this->session_id = $result->sessionId;

            return $this->session_id;

        } catch (Exception $e) {

            Log::info($e->getMessage());

            throw new Exception($e->getMessage());

        }


    }

    /**
     * Get voucher by ID
     * @param array|null $voucher_id
     *
     * @return mixed
     * @throws Exception
     */
    public function getPdf( array $voucher_id = null): mixed
    {

        try {

            $this->parameters = array(
                'paperType'  => 1,
                'perVoucher' => false,
                'sessionID'  => $this->session_id,
                'voucherIDs' => $voucher_id ?? [ $this->created->outListPod->BOL->voucher_code ],
            );

            return $this->client->GetBOLPdf($this->parameters)->GetBOLPdfResult->Voucher->pdf;

        } catch (Exception $e) {

            Log::info($e->getMessage());

           throw new Exception($e->getMessage());

        }

    }


    /**
     * @throws Exception
     */
    public function create($bol_array)
    {

        try {

            $Parameters = array(
                'inListPod' => $bol_array,
                'sessionID' => $this->session_id,
                'tableFlag' => 0
            );

            $this->created =$this->client->CreateBOL($Parameters);

            return $this->created;

        } catch (Exception $e) {

            Log::info($e->getMessage());

            throw new Exception($e->getMessage());
        }


    }

    /**
     * @throws Exception
     * @returns bool
     */
    public function created(): bool
    {
        return isset($this->created->outListPod->BOL);

    }

    /**
     * @throws Exception
     * @returns string
     */
    public function getCreatedVoucherCode(): ?string
    {
        return $this->created->outListPod->BOL->voucher_code ;

    }

    /**
     * @throws Exception
     */
    public function cancel($voucher_id)
    {

        try {

            $Parameters = array(
                'sessionID' => $this->session_id,
                'voucherID' => $voucher_id
            );

            return $this->client->CancelBOL($Parameters);

        } catch (Exception $e) {

            throw new Exception($e->getMessage());

        }


    }


    /**
     * @throws Exception
     */
    public function getVouchers( $dateFrom, $dateTo)
    {

        try {

            $Parameters = array(
                'dateFrom'  => $dateFrom,
                'dateTo'    => $dateTo,
                'sessionID' => $this->session_id,
            );

            return $this->client->GetConsignmentsByDate($Parameters);

        } catch (Exception $e) {

            throw new Exception($e->getMessage());

        }

    }

    /**
     * @throws Exception
     */
    public function trace($voucherID)
    {

        try {

            $Parameters = array(

                'sessionID' => $this->session_id,
                'VoucherID' => $voucherID

            );

            return $this->client->GetTraceByVoucher($Parameters);

        } catch (Exception $e) {

            Log::info($e->getMessage());

            throw new Exception($e->getMessage());

        }

    }


    /**
     * @throws Exception
     */
    public function GetBranch($zip )
    {

        try {

            $Parameters = array(
                'language'  => 1,
                'sessionID' => $this->session_id,
                'zipCode'   => $zip,
                'area'      => '',
            );

            return $this->client->GetBranches($Parameters);

        } catch (Exception $e) {

            Log::info($e->getMessage());

            throw new Exception($e->getMessage());

        }


    }

}
