# 🧪 Postman Tests - Property Comparison

**Collection Name:** RentHub - Property Comparison  
**Base URL:** `http://localhost/api/v1`

---

## 📋 Environment Variables

Create these in Postman Environment:

```json
{
  "base_url": "http://localhost/api/v1",
  "session_id": "session-test-{{$timestamp}}",
  "token": "",
  "property_id_1": "1",
  "property_id_2": "2",
  "property_id_3": "3",
  "property_id_4": "4"
}
```

---

## Test 1: Add Property to Comparison (Guest)

**Request:**
```
POST {{base_url}}/property-comparison/add
```

**Headers:**
```
Content-Type: application/json
X-Session-Id: {{session_id}}
```

**Body (JSON):**
```json
{
  "property_id": {{property_id_1}}
}
```

**Expected Response (200):**
```json
{
  "message": "Property added to comparison",
  "property_ids": [1],
  "session_id": "session-test-1699123456"
}
```

**Tests:**
```javascript
pm.test("Status is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Response has session_id", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.session_id).to.exist;
    pm.environment.set("session_id", jsonData.session_id);
});

pm.test("Property added to list", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.property_ids).to.be.an('array');
    pm.expect(jsonData.property_ids.length).to.be.at.least(1);
});
```

---

## Test 2: Add Second Property

**Request:**
```
POST {{base_url}}/property-comparison/add
```

**Headers:**
```
Content-Type: application/json
X-Session-Id: {{session_id}}
```

**Body (JSON):**
```json
{
  "property_id": {{property_id_2}}
}
```

**Expected Response (200):**
```json
{
  "message": "Property added to comparison",
  "property_ids": [1, 2],
  "session_id": "session-test-1699123456"
}
```

**Tests:**
```javascript
pm.test("Status is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Has 2 properties", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.property_ids.length).to.equal(2);
});
```

---

## Test 3: Get Comparison List

**Request:**
```
GET {{base_url}}/property-comparison
```

**Headers:**
```
X-Session-Id: {{session_id}}
```

**Expected Response (200):**
```json
{
  "property_ids": [1, 2],
  "properties": [
    {
      "id": 1,
      "title": "Luxury Villa",
      "type": "villa",
      "price_per_night": 150,
      "price_per_month": 3500,
      "bedrooms": 3,
      "bathrooms": 2,
      "guests": 6,
      "area_sqm": 120,
      "city": "Bucharest",
      "country": "Romania",
      "images": ["image1.jpg"],
      "amenities": ["WiFi", "Pool"],
      "average_rating": 4.8,
      "review_count": 45,
      "owner": {
        "id": 5,
        "name": "John Doe",
        "avatar": "avatar.jpg"
      }
    }
  ],
  "count": 2
}
```

**Tests:**
```javascript
pm.test("Status is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Has properties array", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.properties).to.be.an('array');
});

pm.test("Count matches array length", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.count).to.equal(jsonData.property_ids.length);
});

pm.test("Properties have required fields", function () {
    var jsonData = pm.response.json();
    if (jsonData.properties.length > 0) {
        var property = jsonData.properties[0];
        pm.expect(property).to.have.property('id');
        pm.expect(property).to.have.property('title');
        pm.expect(property).to.have.property('price_per_night');
        pm.expect(property).to.have.property('city');
    }
});
```

---

## Test 4: Add Third and Fourth Properties

**Request 1:**
```
POST {{base_url}}/property-comparison/add
```

**Body:**
```json
{
  "property_id": {{property_id_3}}
}
```

**Request 2:**
```
POST {{base_url}}/property-comparison/add
```

**Body:**
```json
{
  "property_id": {{property_id_4}}
}
```

**Tests:**
```javascript
pm.test("Has 4 properties (max limit)", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.property_ids.length).to.equal(4);
});
```

---

## Test 5: Try to Add Fifth Property (Should Fail)

**Request:**
```
POST {{base_url}}/property-comparison/add
```

**Headers:**
```
Content-Type: application/json
X-Session-Id: {{session_id}}
```

**Body:**
```json
{
  "property_id": 5
}
```

**Expected Response (400):**
```json
{
  "message": "Maximum 4 properties can be compared at once"
}
```

**Tests:**
```javascript
pm.test("Status is 400", function () {
    pm.response.to.have.status(400);
});

pm.test("Error message about maximum", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.message).to.include("Maximum");
    pm.expect(jsonData.message).to.include("4");
});
```

---

## Test 6: Get Detailed Comparison

**Request:**
```
POST {{base_url}}/property-comparison/compare
```

**Headers:**
```
Content-Type: application/json
```

**Body:**
```json
{
  "property_ids": [1, 2, 3]
}
```

**Expected Response (200):**
```json
{
  "properties": [
    {
      "id": 1,
      "title": "Luxury Villa",
      "description": "Beautiful villa...",
      "type": "villa",
      "furnishing_status": "fully_furnished",
      "price_per_night": 150,
      "price_per_week": 900,
      "price_per_month": 3500,
      "cleaning_fee": 50,
      "security_deposit": 300,
      "bedrooms": 3,
      "bathrooms": 2,
      "guests": 6,
      "area_sqm": 120,
      "square_footage": 1292,
      "city": "Bucharest",
      "country": "Romania",
      "amenities": [
        {"id": 1, "name": "WiFi", "icon": "wifi"},
        {"id": 2, "name": "Pool", "icon": "pool"}
      ],
      "average_rating": 4.8,
      "review_count": 45,
      "rating_breakdown": {
        "cleanliness": 4.9,
        "accuracy": 4.7,
        "communication": 4.8,
        "location": 4.9,
        "checkin": 4.8,
        "value": 4.7
      },
      "owner": {
        "id": 5,
        "name": "John Doe",
        "avatar": "avatar.jpg",
        "joined_at": "2023-01-15"
      }
    }
  ],
  "comparison_matrix": [
    {
      "feature": "Price per Night",
      "type": "currency",
      "values": [150, 120, 180]
    },
    {
      "feature": "Bedrooms",
      "type": "number",
      "values": [3, 2, 4]
    }
  ]
}
```

**Tests:**
```javascript
pm.test("Status is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Has properties array", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.properties).to.be.an('array');
    pm.expect(jsonData.properties.length).to.be.at.least(2);
});

pm.test("Has comparison_matrix", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.comparison_matrix).to.be.an('array');
});

pm.test("Matrix has correct structure", function () {
    var jsonData = pm.response.json();
    if (jsonData.comparison_matrix.length > 0) {
        var row = jsonData.comparison_matrix[0];
        pm.expect(row).to.have.property('feature');
        pm.expect(row).to.have.property('type');
        pm.expect(row).to.have.property('values');
        pm.expect(row.values).to.be.an('array');
    }
});

pm.test("Properties have rating breakdown", function () {
    var jsonData = pm.response.json();
    if (jsonData.properties.length > 0) {
        var property = jsonData.properties[0];
        pm.expect(property.rating_breakdown).to.exist;
        pm.expect(property.rating_breakdown.cleanliness).to.exist;
    }
});
```

---

## Test 7: Remove Property from Comparison

**Request:**
```
DELETE {{base_url}}/property-comparison/remove/{{property_id_2}}
```

**Headers:**
```
X-Session-Id: {{session_id}}
```

**Expected Response (200):**
```json
{
  "message": "Property removed from comparison",
  "property_ids": [1, 3, 4]
}
```

**Tests:**
```javascript
pm.test("Status is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Property removed", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.property_ids).to.not.include(parseInt(pm.environment.get("property_id_2")));
});

pm.test("Count decreased", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.property_ids.length).to.be.lessThan(4);
});
```

---

## Test 8: Clear All Comparisons

**Request:**
```
DELETE {{base_url}}/property-comparison/clear
```

**Headers:**
```
X-Session-Id: {{session_id}}
```

**Expected Response (200):**
```json
{
  "message": "Comparison list cleared"
}
```

**Tests:**
```javascript
pm.test("Status is 200", function () {
    pm.response.to.have.status(200);
});

pm.test("Success message", function () {
    var jsonData = pm.response.json();
    pm.expect(jsonData.message).to.include("cleared");
});
```

**Verify Clear:**
```
GET {{base_url}}/property-comparison
Headers: X-Session-Id: {{session_id}}
```

Expected empty response:
```json
{
  "property_ids": [],
  "properties": [],
  "count": 0
}
```

---

## Test 9: Authenticated User Test

**Step 1: Login**
```
POST {{base_url}}/login
```

**Body:**
```json
{
  "email": "tenant@example.com",
  "password": "password123"
}
```

**Save token:**
```javascript
pm.test("Login successful", function () {
    var jsonData = pm.response.json();
    pm.environment.set("token", jsonData.token);
});
```

**Step 2: Add Property as Authenticated User**
```
POST {{base_url}}/property-comparison/add
```

**Headers:**
```
Content-Type: application/json
Authorization: Bearer {{token}}
```

**Body:**
```json
{
  "property_id": 1
}
```

**Tests:**
```javascript
pm.test("Authenticated user can add", function () {
    pm.response.to.have.status(200);
    var jsonData = pm.response.json();
    pm.expect(jsonData.property_ids).to.include(1);
});
```

**Step 3: Get Comparison (No Session ID needed)**
```
GET {{base_url}}/property-comparison
```

**Headers:**
```
Authorization: Bearer {{token}}
```

---

## Test 10: Validation Tests

### Test 10.1: Invalid Property ID
```
POST {{base_url}}/property-comparison/add
```

**Body:**
```json
{
  "property_id": 99999
}
```

**Expected:** 422 Validation Error

### Test 10.2: Missing Property ID
```
POST {{base_url}}/property-comparison/add
```

**Body:**
```json
{}
```

**Expected:** 422 Validation Error

### Test 10.3: Compare with Less than 2 Properties
```
POST {{base_url}}/property-comparison/compare
```

**Body:**
```json
{
  "property_ids": [1]
}
```

**Expected:** Should work but UI requires minimum 2

---

## 📊 Collection Runner Sequence

Run tests in this order:

1. ✅ Test 1: Add first property
2. ✅ Test 2: Add second property
3. ✅ Test 3: Get list
4. ✅ Test 4: Add third & fourth
5. ✅ Test 5: Try fifth (fail)
6. ✅ Test 6: Get detailed comparison
7. ✅ Test 7: Remove one property
8. ✅ Test 3: Get list again (verify removal)
9. ✅ Test 8: Clear all
10. ✅ Test 3: Get list (verify empty)
11. ✅ Test 9: Authenticated user
12. ✅ Test 10: Validation tests

---

## 🎯 Success Criteria

All tests should pass with:
- ✅ Correct status codes
- ✅ Valid JSON responses
- ✅ Required fields present
- ✅ Proper error messages
- ✅ Session persistence
- ✅ Data consistency

---

## 📝 Notes

- **Session ID:** Auto-generated or use custom
- **Token:** Get from login endpoint
- **Property IDs:** Use valid IDs from your database
- **Base URL:** Change for staging/production

---

## 🔄 Import to Postman

1. Copy this collection structure
2. Create new collection in Postman
3. Add environment variables
4. Create requests as documented
5. Add tests to each request
6. Run collection

**Or use Postman API:**
- Export from here as JSON
- Import into Postman

---

**Collection Complete!** 🎉

Run the collection to validate the entire Property Comparison API.
