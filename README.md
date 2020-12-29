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

**Property Add**
```
/api/property/add/ (POST)
```
Required Parameters
```
"title": "This is a testing Property",
"description": "This is a description",
"image": "test.jpg",
"price": "200",
"number_of_bedrooms": "2",
"number_of_bathrooms": "1",
"location": "",
"type": "appartment",
"amenities": "pool,wi-fi, garden" (comma separated)
```

