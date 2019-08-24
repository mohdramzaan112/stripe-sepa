

Things to keep in mind before SEPA Direct Debit Payment Gateway Integration:

* include dbconfig file and stripe-php (download and then put) library
* include stripe library next to body start tag
* Always use stripe element for form creation which is secure
* Activate the account to use SEPA 
* If your country is not in drop down then send request
* payment status remain pending for some time and then updated as successful
* for getting updated status of payment we use webhooks
* make sure to update publishable and secret keys when go to stripe live
