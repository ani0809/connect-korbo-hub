<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use MyFatoorah\Library\API\Payment\MyFatoorahPayment;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentEmbedded;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentStatus;
use MyFatoorah\Library\MyFatoorah;

class MyFatoorahController extends Controller
{
    public $mfConfig = [];

    public function __construct()
    {
        $this->mfConfig = [
            'apiKey' => config('myfatoorah.api_key'),
            'isTest' => config('myfatoorah.test_mode'),
            'countryCode' => config('myfatoorah.country_iso'),
        ];
    }

    public function index()
    {
        try {
            $paymentId = request('pmid') ?: 0;
            $sessionId = request('sid') ?: null;
            $orderId = request('oid') ?: 147;
            $curlData = $this->getPayLoadData($orderId);

            $mfObj = new MyFatoorahPayment($this->mfConfig);
            $payment = $mfObj->getInvoiceURL($curlData, $paymentId, $orderId, $sessionId);

            return redirect($payment['invoiceURL']);
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            return response()->json(['IsSuccess' => 'false', 'Message' => $exMessage]);
        }
    }

    private function getPayLoadData($orderId = null)
    {
        $callbackURL = route('myfatoorah.callback');
        $order = $this->getTestOrderData($orderId);

        return [
            'CustomerName' => 'FName LName',
            'InvoiceValue' => $order['total'],
            'DisplayCurrencyIso' => $order['currency'],
            'CustomerEmail' => 'test@test.com',
            'CallBackUrl' => $callbackURL,
            'ErrorUrl' => $callbackURL,
            'MobileCountryCode' => '+965',
            'CustomerMobile' => '12345678',
            'Language' => 'en',
            'CustomerReference' => $orderId,
            'SourceInfo' => 'Laravel ' . app()::VERSION . ' - MyFatoorah Package ' . MYFATOORAH_LARAVEL_PACKAGE_VERSION,
        ];
    }

    public function callback()
    {
        try {
            $paymentId = request('paymentId');

            $mfObj = new MyFatoorahPaymentStatus($this->mfConfig);
            $data = $mfObj->getPaymentStatus($paymentId, 'PaymentId');

            $message = $this->getTestMessage($data->InvoiceStatus, $data->InvoiceError);
            $response = ['IsSuccess' => true, 'Message' => $message, 'Data' => $data];
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            $response = ['IsSuccess' => 'false', 'Message' => $exMessage];
        }

        return response()->json($response);
    }

    public function checkout()
    {
        try {
            $orderId = request('oid') ?: 147;
            $order = $this->getTestOrderData($orderId);
            $customerId = request('customerId');
            $userDefinedField = config('myfatoorah.save_card') && $customerId ? "CK-$customerId" : '';

            $mfObj = new MyFatoorahPaymentEmbedded($this->mfConfig);
            $paymentMethods = $mfObj->getCheckoutGateways($order['total'], $order['currency'], config('myfatoorah.register_apple_pay'));

            if (empty($paymentMethods['all'])) {
                throw new Exception('noPaymentGateways');
            }

            $mfSession = $mfObj->getEmbeddedSession($userDefinedField);
            $isTest = $this->mfConfig['isTest'];
            $vcCode = $this->mfConfig['countryCode'];
            $countries = MyFatoorah::getMFCountries();
            $jsDomain = ($isTest) ? $countries[$vcCode]['testPortal'] : $countries[$vcCode]['portal'];

            return view('myfatoorah.checkout', compact('mfSession', 'paymentMethods', 'jsDomain', 'userDefinedField'));
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            return view('myfatoorah.error', compact('exMessage'));
        }
    }

    public function webhook(Request $request)
    {
        try {
            $secretKey = config('myfatoorah.webhook_secret_key');
            if (empty($secretKey)) {
                return response(null, 404);
            }

            $mfSignature = $request->header('MyFatoorah-Signature');
            if (empty($mfSignature)) {
                return response(null, 404);
            }

            $body = $request->getContent();
            $input = json_decode($body, true);
            if (empty($input['Data']) || empty($input['EventType']) || $input['EventType'] != 1) {
                return response(null, 404);
            }

            if (!MyFatoorah::isSignatureValid($input['Data'], $secretKey, $mfSignature, $input['EventType'])) {
                return response(null, 404);
            }

            $result = $this->changeTransactionStatus($input['Data']);
            return response()->json($result);
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            return response()->json(['IsSuccess' => false, 'Message' => $exMessage]);
        }
    }

    private function changeTransactionStatus($inputData)
    {
        $orderId = $inputData['CustomerReference'];
        $invoiceId = $inputData['InvoiceId'];

        if ($inputData['TransactionStatus'] == 'SUCCESS') {
            $status = 'Paid';
            $error = '';
        } else {
            $mfObj = new MyFatoorahPaymentStatus($this->mfConfig);
            $data = $mfObj->getPaymentStatus($invoiceId, 'InvoiceId');
            $status = $data->InvoiceStatus;
            $error = $data->InvoiceError;
        }

        $message = $this->getTestMessage($status, $error);
        return ['IsSuccess' => true, 'Message' => $message, 'Data' => $inputData];
    }

    private function getTestOrderData($orderId)
    {
        return [
            'total' => 15,
            'currency' => 'KWD',
        ];
    }

    private function getTestMessage($status, $error)
    {
        if ($status == 'Paid') {
            return 'Invoice is paid.';
        } elseif ($status == 'Failed') {
            return 'Invoice is not paid due to ' . $error;
        } elseif ($status == 'Expired') {
            return $error;
        }
    }
}
