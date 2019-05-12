
## Installation

To run this project, ensure you have a running installation of virtual box and vagrant.
Clone the project and cd into the project.

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
