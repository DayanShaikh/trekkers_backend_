<?php

namespace App\Services;

use App\Models\ConfigVariable;
use nl\rabobank\gict\payments_savings\omnikassa_sdk\model\Money;
use nl\rabobank\gict\payments_savings\omnikassa_sdk\model\OrderItem;
use nl\rabobank\gict\payments_savings\omnikassa_sdk\model\PaymentBrand;
use nl\rabobank\gict\payments_savings\omnikassa_sdk\model\PaymentBrandForce;
use nl\rabobank\gict\payments_savings\omnikassa_sdk\model\ProductType;
use nl\rabobank\gict\payments_savings\omnikassa_sdk\model\request\MerchantOrder;
use nl\rabobank\gict\payments_savings\omnikassa_sdk\model\Address;
use nl\rabobank\gict\payments_savings\omnikassa_sdk\model\CustomerInformation;
use nl\rabobank\gict\payments_savings\omnikassa_sdk\model\VatCategory; 
use nl\rabobank\gict\payments_savings\omnikassa_sdk\model\signing\SigningKey;
use nl\rabobank\gict\payments_savings\omnikassa_sdk\connector\TokenProvider;
use nl\rabobank\gict\payments_savings\omnikassa_sdk\endpoint\Endpoint;

class PaymentService{
	
    private $rokServerUrl = 'https://betalen.rabobank.nl/omnikassa-api-sandbox/';
    //private $rokServerUrl = 'https://betalen.rabobank.nl/omnikassa-api/';
    private $inMemoryTokenProvider;
    private $signingKey;
	
    public function __construct(){
		
        //$ideal_signing_key = ConfigVariable::where("config_key", "ideal_signing_key")->first()->value;
        //$ideal_referesh_token = ConfigVariable::where("config_key", "ideal_referesh_token")->first()->value;
        $ideal_referesh_token = "eyJraWQiOiJIUDFYU2tYY1B2MWxNNTBvT3dkSVVHR3h5N1Q3MG1nazZJRzhRQktMUm9JPSIsImFsZyI6IlJTMjU2In0.eyJta2lkIjoxMTM0NywiZW52IjoiUyIsImV4cCI6NzI1ODAyODQwMH0.pIk9ib5b9F6-gU7M5m7R0jA4zWaufCSsTTY8t83Jh_sW1ekjL72SQRLJC6W1lzZ77DS061HeEn2cD4f9PlczjfOi6x9qOyaUeJb0rXpFz3L19ZuUXZsJz0r2KN0676pkIGSqdEZCEPFjcbTQfkidA2PCT7y-q-ER_ObVHkcfWJTBYg-9wPP8asfAn3Ij9YP5GyJixEaXTbU3owKXUPGFa1KyA_zC-96r-OVTnK2vU6juWImni0Lr5zK41X-g_Dm4lXr1jgS3o-cjxQOIbR6MrDw6Rw-YE3s1hL9h-iNaXhmXILOD95K952v8G6s6UOoWgS4bQHJ9VTkW3pDlwNwT2Q";
		$ideal_signing_key = "TOCm407TVOxDd84p0ZTPzVsKnClhNnrHW/gkxDdCjFc=";
		$this->signingKey = new SigningKey(base64_decode( $ideal_signing_key ));
		$this->inMemoryTokenProvider = new InMemoryTokenProvider( $ideal_referesh_token );
    }


    public function createUrl($order){
        //return $order;
		//return $order->orderable->tripBooking;
        try{
            $booking = (($order->orderable_type == 'App\Models\TripBooking') || ($order->orderable_type == 'App\Models\Reservation')) ? $order->orderable : $order->orderable->tripBooking;
        //    return $booking;
			$orderItems = [
                OrderItem::createFrom(
                    [
                        'id' => $order->orderable->id,
                        'name' => ($order->orderable->type == 'App\Models\TripBooking' ? ($order->orderable->trip->location->title . '(' + $order->orderable->trip->start_date->format('m-d-Y') + ')') : ($order->orderable->locationAddon ? $order->orderable->locationAddon->title:'Test')),
                        //'description' => $order->orderable->type == 'App\Models\Booking' ? ($order->orderable->trip->location->title . '(' + date('d-m-Y', strtotime($order->orderable->trip->start_date)) + ')') : ($order->orderable->trip->location->title . '(' + date('d-m-Y', strtotime($order->orderable->trip->start_date)) + ')'),
                        'description' => ($order->orderable->type == 'App\Models\TripBooking' ? ($order->orderable->trip->location->title . '(' + $order->orderable->trip->start_date->format('m-d-Y') + ')') : ($order->orderable->locationAddon ? $order->orderable->locationAddon->title:'')),
						'quantity' => 1,
                        'amount' => Money::fromDecimal('EUR', $order->amount),
                        'tax' => Money::fromDecimal('EUR', 0),
                        'category' => ProductType::DIGITAL,
                        'vatCategory' => VatCategory::HIGH
                    ]
                )
            ]; 
            $shippingDetail = new Address( $booking->child_firstname, '', $booking->child_lastname, $booking->address, $booking->postcode, $booking->city, 'NL', $booking->house_number, '' );
            $billingDetails = $shippingDetail;
            $customerInformation = new CustomerInformation( $booking->email, $booking->child_dob->format('d-m-Y'), $booking->gender ? 'F':'M', '.', $booking->telephone ); 
            $order = new MerchantOrder($order->id, 'Order ID: '.$booking->id, $orderItems, Money::fromDecimal('EUR', $order->amount), $shippingDetail, 'NL', url('/rabobank-response'), PaymentBrand::IDEAL, PaymentBrandForce::FORCE_ONCE, $customerInformation, $billingDetails);
            $endpoint = Endpoint::createInstance($this->rokServerUrl, $this->signingKey, $this->inMemoryTokenProvider);
            $redirectUrl = $endpoint->announceMerchantOrder($order);
            return $redirectUrl;
        }
        catch(Exception $e){
            return $e->getMessage();
        }
    }
}

class InMemoryTokenProvider extends TokenProvider {
	private $map = array(); 
 	public function __construct($refreshToken){
		$this->setValue('REFRESH_TOKEN', $refreshToken);	
	} 
	protected function getValue($key){
		return array_key_exists($key, $this->map) ? $this->map[$key] : null;
	}
	protected function setValue($key, $value){
		$this->map[$key] = $value;
	} 
 	protected function flush(){ 
 
    }
}
