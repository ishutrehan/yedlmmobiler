# yedlmmobiler

**User Registration API**
```
/api/user/register (POST)
```
Required Parameters
```
"name": "",
"email": "",
"password": "",
"phone": "",
"role": ""
```
**User Login API**
```
/api/user/login (POST)
```
Required Parameters
```
"email": "",
"password": ""
```
**User Profile Update**
```
/api/user/update/{id} (POST)
```

**User Change Password**
```
/api/user/password/update/{id} (POST)
```
Required Parameters
```
"password": ""
```

**Load All Properties and Search Properties**
```
/api/property/search/
```
For Filter Type use below parameter  
```
type: "string"
```
For Filter price use below parameters 
```
price_min: "number"
price_max: "number"
```
For Filter bedrooms use below parameters 
```
bedroom_min: "number"
bedroom_max: "number"
```


**Property Add**
```
/api/property/add/ (POST)
```
Required Parameters
```
"title": "This is a testing Property",
"description": "This is a description",
"image": "",
"price": "200",
"number_of_bedrooms": "2",
"number_of_bathrooms": "1",
"location": "",
"area": "",
"type": "appartment",
"listed_by": (current user id)
"amenities": "pool,wi-fi, garden" (comma separated)
```

