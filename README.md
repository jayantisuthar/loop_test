
## About Test Project

## Installation : 
Add the following key values in .env 

```
#LOOP
LOOP_USERNAME=loop
LOOP_PASSWORD=backend_dev
```

- setup the database for the app and run the below command.
- ``` php artisan migrate ```
 
- To import customer run the following command. : as of now the implemetaion is in synchronius manner but for huge data set we can implement the **Batch Job Processing** 
- ``` php artisan import:customers ```

- To import products run the following commands
- ``` php artisan import:products ```
---
#Postman request collection 
[Download Postman Collection File](postman_collection.json)

---

## API resource endpoint for order api's where we can do the CRUD operation 
here `{$order}` is the id of the order

- `GET (http://127.0.0.1:8000/api/order)` : this api return all the order and if the filter is applied it return the customer 
```
Request ( Optional ) 
{
  customer : 5
}
```   
---
- `GET (http://127.0.0.1:8000/api/order/{$order})` : this api return single order details with all the products attached
---
- `POST (http://127.0.0.1:8000/api/order/{$order})` : create an order for customer with product we want to attach to order
```
Request 
{
    "customer": 6,
    "products": [1, 2, 3]
}
```   
---
- `PATCH (http://127.0.0.1:8000/api/order/{$order})` : this api update an order products , it basically sync products  so removing old product that are not in update request and adding the new product 
  - Here I have not allowed to update the other order details like `payed`, `customer` as generally one customer order we can't assign to another customer and payment is also can't be marked via the api 
```
Request 
{
    "products": [5, 2, 6]
}
```   
---  
- `DELETE (http://127.0.0.1:8000/api/order/{$order})` : delete the order and it's attached products, as the pivot table is attached via the cascade delete so on deleting the order details , it auto delete the entries attached in pivot table 
---  

- `POST (http://127.0.0.1:8000/api/order/{$order}/add)` : allow to attach new product to an existing order without removing the old products 
```
Request 
{
    "product_id": 55
}
```
---  
- `POST (http://127.0.0.1:8000/api/order/{$order}/pay)` : allow to make the payment of an order , if no `payment_method`  key is attached in request then it will take by default **_[Super Payment Provider](https://superpay.view.agentur-loop.com/pay)_** , for other payment gateway to use in payment method , we have to attach the key with request 
```
Request 
{
    "payment_method": "stripe"  
}
```
---

## In order to attach new payment gateway 
To add another payment gateway, you need to add the relative classes in the `PaymentServiceProvider`'s register method and implement interface `\App\Interfaces\PaymentService` onto that class along with methods in the interfaces.

For example: To implement Stripe, our `PaymentServiceProvider`'s register method could look something like this:
```
   public function register(): void
   {
       $this->app->bind(PaymentService::class, function ($app) {
           $gateway = request()->payment_method ?? 'default';
           switch ($gateway) {
                case 'stripe':
                    return new StripePaymentGateway();
               default:
                   return new SuperPaymentProvider();
           }
       });
   }     
```
---
## Log Details

System will generate two log files:
1. `csv_import-{date}.log` : This file will have log details of customers and products import.
2. `order_payments-{date}.log` : This file will have log details related to the order payment.

