
## Installation

To run this project, ensure you have a running installation of virtual box, vagrant and composer.
Clone the project and cd into the project.
Run the below command to install homestead directly into your project.
``` 
composer require laravel/homestead --dev
```
Once homestead is installed into your project, run 
``` 
php vendor/bin/homestead make
```
to generate a Homestead.yaml file

create a .env file and copy the content of the .env.example file. Make sure to change your configurations to suit your needs

Run 
```
vagrant up
```

To run migrations, ssh into the vagrant machine by running the following command;
```
vagrant ssh
```

change into the code directory, then run the below command

```
php artisan migrate
```

## API endpoints
/api/register This is a POST request. Below is the expected body of the request
```
{
    "name":"name of user",
    "email":"email@user.com",
    "password":"strongpassword",
    "c_password":"strongpassword"
}
``` 
The response is success on successful user registration or error on failure



/api/login This is a POST request. The expected body of the request is as follows
```
{
    "email":"email@user.com",
    "password":"strongpassword"
}
```
On successful login, the system shall respond with a Bearer token. On failure the system shall repond with a status 10

/api/createsim This is a POST request that creates a sim card. This endpoint requires a Bearer token on the header. The expected body of the request is as follows.
```
{
    "ki":"A 20 character string"
}
```
on successful simcard creation the system shall respond with a status of 0. On error creating a simcard, the system shall respond with a status 10.
If the sim card already exists, the system shall repond with the status code 1

/api/activatesim This is POST endpoint that activates a simcard by linking it to an msisdn and changing the sim status to active. The expected request body
is as follows
```
{
    "iccid":"the iccid of a given simcard"
    "msisdn":"The msisdn to be linked to this iccid"
}
```
The response a status 0 on success. If the sim is already active, the expected status is 2. If the simcard does not exist, the status shall be 1

/api/subscriberinfo This is a POST endpoint that is used to query a subscribers information

The expected request body is as follows
```
{
    "msisdn":"254712345678"
}
```
The response shall be of status 1 with the subscriber information or status 0 if the msisdn does not exist.

/api/adjustbalance This is a POST endpoint that adjusts the balance of a given msisdn number. The expected request body is as follows;
```
{
    "msisdn":"254712345678",
    "transactiontype":"1",
    "amount":"10"
}
```
The transaction type is either 1 for topup or 0 for debit.
The response shall be 1 for a successful transaction or 0 for a failed transaction

