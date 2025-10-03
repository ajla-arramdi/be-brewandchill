# Order Management API - Updated Structure

This document describes the API endpoints for the order management system with guest order functionality.

## Base URL
`/api/orders`

## Endpoints

### 1. Get All Orders
- **Method**: GET
- **URL**: `/api/orders`
- **Description**: Retrieve all orders with their associated user and order items
- **Authentication**: Required (Admin/Cashier only)
- **Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": null,
      "guest_name": "John Doe",
      "table_number": "Table 1",
      "status": "paid",
      "total_price": "25.50",
      "is_guest_order": true,
      "created_at": "2023-10-03T10:30:00.000000Z",
      "updated_at": "2023-10-03T10:45:00.000000Z",
      "user": null,
      "orderItems": [
        {
          "id": 1,
          "order_id": 1,
          "menu_id": 1,
          "quantity": 2,
          "price": "12.75",
          "menu": {
            "id": 1,
            "name": "Coffee",
            "price": "12.75"
          }
        }
      ]
    }
  ]
}
```

### 2. Create New Order (Guest Users - No Authentication Required)
- **Method**: POST
- **URL**: `/api/orders`
- **Description**: Create a new order for guest users
- **Authentication**: Not Required
- **Request Body**:
```json
{
  "guest_name": "John Doe",
  "table_number": "Table 5",
  "items": [
    {
      "menu_id": 1,
      "quantity": 2
    },
    {
      "menu_id": 3,
      "quantity": 1
    }
  ]
}
```
- **Validation**:
  - `guest_name` is required (string, max 255 chars) - for guest orders
  - `table_number` is required (string, max 255 chars)
  - `items` is required (array, minimum 1 item)
  - Each item must have `menu_id` (must exist in menus table) and `quantity` (integer, minimum 1)

- **Response**:
```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "id": 2,
    "user_id": null,
    "guest_name": "John Doe",
    "table_number": "Table 5",
    "status": "pending",
    "total_price": "38.25",
    "is_guest_order": true,
    "created_at": "2023-10-03T11:00:00.000000Z",
    "updated_at": "2023-10-03T11:00:00.000000Z",
    "user": null,
    "orderItems": [
      {
        "id": 2,
        "order_id": 2,
        "menu_id": 1,
        "quantity": 2,
        "price": "12.75",
        "menu": {
          "id": 1,
          "name": "Coffee",
          "price": "12.75"
        }
      },
      {
        "id": 3,
        "order_id": 2,
        "menu_id": 3,
        "quantity": 1,
        "price": "12.75",
        "menu": {
          "id": 3,
          "name": "Tea",
          "price": "12.75"
        }
      }
    ]
  }
}
```

### 3. Get Specific Order
- **Method**: GET
- **URL**: `/api/orders/{id}`
- **Description**: Retrieve a specific order by ID
- **Authentication**: Required (Admin/Cashier only)
- **Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": null,
    "guest_name": "John Doe",
    "table_number": "Table 1",
    "status": "paid",
    "total_price": "25.50",
    "is_guest_order": true,
    "created_at": "2023-10-03T10:30:00.000000Z",
    "updated_at": "2023-10-03T10:45:00.000000Z",
    "user": null,
    "orderItems": [
      {
        "id": 1,
        "order_id": 1,
        "menu_id": 1,
        "quantity": 2,
        "price": "12.75",
        "menu": {
          "id": 1,
          "name": "Coffee",
          "price": "12.75"
        }
      }
    ]
  }
}
```

### 4. Update Order (Admin Only - Non-Status Fields)
- **Method**: PUT
- **URL**: `/api/orders/{id}`
- **Description**: Update order details (excluding status - only admin can update non-status fields)
- **Authentication**: Required (Admin only)
- **Request Body**:
```json
{
  "table_number": "Table 10",
  "user_id": 2,
  "guest_name": "Jane Smith"
}
```
- **Validation**:
  - `table_number` is optional (string, max 255 chars)
  - `user_id` is optional (must exist in users table)
  - `guest_name` is optional (string, max 255 chars)
  - `status` field is NOT allowed to be updated through this endpoint
- **Response**:
```json
{
  "success": true,
  "message": "Order updated successfully",
  "data": {
    "id": 1,
    // ... order data
  }
}
```

### 5. Mark Order as Paid
- **Method**: PATCH
- **URL**: `/api/orders/{id}/mark-paid`
- **Description**: Mark an order as paid (changes status to 'paid')
- **Authentication**: Required (Cashier only)
- **Response**:
```json
{
  "success": true,
  "message": "Order marked as paid successfully",
  "data": {
    "id": 1,
    "status": "paid",
    // ... rest of order data
  }
}
```

### 6. Mark Order as Completed
- **Method**: PATCH
- **URL**: `/api/orders/{id}/mark-completed`
- **Description**: Mark an order as completed (only if status is 'paid')
- **Authentication**: Required (Cashier only)
- **Response**:
```json
{
  "success": true,
  "message": "Order marked as completed successfully",
  "data": {
    "id": 1,
    "status": "completed",
    // ... rest of order data
  }
}
```

## Order Status Flow

1. **pending** - Initial status when order is created
2. **paid** - Set when payment is processed (can be marked with `/mark-paid` endpoint)
3. **completed** - Final status when order is fulfilled (can be marked with `/mark-completed` endpoint)

## Guest Order Flow

1. Guest user provides their name and table number
2. Guest user selects menu items to order
3. Order is created with `guest_name` and `table_number`
4. Order status starts as `pending`
5. Cashier marks order as `paid` when payment is received
6. Cashier marks order as `completed` when order is fulfilled

## Frontend Integration Example

For React applications, you can use the following service to interact with the API:

```javascript
// OrderService.js
import axios from 'axios';

const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

const orderService = {
  // Get all orders (requires admin/cashier authentication)
  getAllOrders: async (token) => {
    try {
      const response = await axios.get(`${API_BASE_URL}/orders`, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Create new order for guests (no authentication required)
  createGuestOrder: async (orderData) => {
    try {
      const response = await axios.post(`${API_BASE_URL}/orders`, orderData);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Mark order as paid (requires cashier authentication)
  markOrderAsPaid: async (orderId, token) => {
    try {
      const response = await axios.patch(`${API_BASE_URL}/orders/${orderId}/mark-paid`, {}, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Mark order as completed (requires cashier authentication)
  markOrderAsCompleted: async (orderId, token) => {
    try {
      const response = await axios.patch(`${API_BASE_URL}/orders/${orderId}/mark-completed`, {}, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  }
};

export default orderService;
```

## Error Handling

All API endpoints return consistent error responses:

```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error information" // Only in 500 errors
}
```

Common status codes:
- 200: Success
- 201: Created
- 400: Bad Request (validation errors)
- 403: Forbidden (Insufficient permissions)
- 404: Not Found
- 500: Server Error