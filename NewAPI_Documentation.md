# Brew & Chill API Documentation - Updated Structure

## Authentication System

### Authentication Overview
- Only **Admin** and **Cashier** users have login accounts
- **Regular users** do not need to register/login - they can directly place orders
- All authenticated users (Admin and Cashier) use Sanctum tokens for authentication

### Roles
- `admin`: Full system access (manage categories, menus, users, view all orders)
- `cashier`: Order processing, payment processing, view all orders
- `user`: (legacy - not used in new system) - can only view their own orders

## Authentication Endpoints

### Login
- **POST** `/api/login`
- **Description**: Authenticate admin or cashier user
- **Request**:
```json
{
  "email": "admin@example.com",
  "password": "yourpassword"
}
```
- **Response**:
```json
{
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com"
  },
  "access_token": "token_value",
  "token_type": "Bearer"
}
```

### Logout
- **POST** `/api/logout`
- **Description**: Logout and revoke current token
- **Authorization**: Bearer Token required
- **Response**: `{"message": "Logged out successfully"}`

### Get Current User
- **GET** `/api/user`
- **Description**: Get authenticated user information
- **Authorization**: Bearer Token required
- **Response**:
```json
{
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com"
  },
  "roles": ["admin"]
}
```

## User Management (Admin Only)

### List Cashiers
- **GET** `/api/users`
- **Description**: Get list of all cashier accounts
- **Authorization**: Admin role required
- **Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 2,
      "name": "Cashier User",
      "email": "cashier@example.com",
      "roles": [
        {
          "id": 2,
          "name": "cashier",
          "display_name": "Cashier",
          "created_at": "...",
          "updated_at": "..."
        }
      ]
    }
  ]
}
```

### Create Cashier
- **POST** `/api/users`
- **Description**: Create a new cashier account
- **Authorization**: Admin role required
- **Request**:
```json
{
  "name": "John Cashier",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```
- **Response**:
```json
{
  "success": true,
  "message": "Cashier created successfully",
  "data": {
    "id": 3,
    "name": "John Cashier",
    "email": "john@example.com",
    "...": "..."
  }
}
```

### Get Cashier
- **GET** `/api/users/{id}`
- **Description**: Get specific cashier information
- **Authorization**: Admin role required
- **Response**:
```json
{
  "success": true,
  "data": {
    "id": 2,
    "name": "Cashier User",
    "email": "cashier@example.com",
    "...": "..."
  }
}
```

### Update Cashier
- **PUT/PATCH** `/api/users/{id}`
- **Description**: Update cashier account information
- **Authorization**: Admin role required
- **Request**:
```json
{
  "name": "Updated Cashier Name",
  "email": "newemail@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

### Delete Cashier
- **DELETE** `/api/users/{id}`
- **Description**: Delete cashier account
- **Authorization**: Admin role required
- **Response**:
```json
{
  "success": true,
  "message": "Cashier deleted successfully"
}
```

## Category Management (Admin Only)

### Categories Endpoints
- **GET** `/api/categories` - List all categories
- **POST** `/api/categories` - Create category
- **GET** `/api/categories/{id}` - Get category
- **PUT** `/api/categories/{id}` - Update category
- **DELETE** `/api/categories/{id}` - Delete category
- **Authorization**: Admin role required for all endpoints

## Menu Management (Admin Only)

### Menus Endpoints
- **GET** `/api/menus` - List all menu items
- **POST** `/api/menus` - Create menu item
- **GET** `/api/menus/{id}` - Get menu item
- **PUT** `/api/menus/{id}` - Update menu item
- **DELETE** `/api/menus/{id}` - Delete menu item
- **Authorization**: Admin role required for all endpoints

## Order Management

### Create Order (Guest Users - No Authentication Required)
- **POST** `/api/orders`
- **Description**: Create a new order for guest users
- **Request**:
```json
{
  "guest_name": "John Doe",
  "table_number": "T1",
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
- **Response**:
```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "id": 1,
    "user_id": null,
    "guest_name": "John Doe",
    "table_number": "T1",
    "status": "pending",
    "total_price": 24.99,
    "is_guest_order": true,
    "order_items": [...],
    "created_at": "..."
  }
}
```

### View Orders (Admin/Cashier Only)
- **GET** `/api/orders`
- **Description**: Get all orders (admin and cashier can view all orders)
- **Authorization**: Bearer Token required (Admin or Cashier role)
- **Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": null,
      "guest_name": "John Doe",
      "table_number": "T1",
      "status": "pending",
      "total_price": 24.99,
      "is_guest_order": true,
      "user": null,
      "order_items": [...],
      "created_at": "..."
    }
  ]
}
```

### View Specific Order (Admin/Cashier Only)
- **GET** `/api/orders/{id}`
- **Description**: Get specific order details
- **Authorization**: Bearer Token required (Admin or Cashier role)
- **Response**: Similar to create order response

### Update Order (Admin Only)
- **PUT** `/api/orders/{id}`
- **Description**: Update order details (excluding status)
- **Authorization**: Admin role required
- **Request**:
```json
{
  "table_number": "T2",
  "user_id": 5,
  "guest_name": "Jane Doe"
}
```

### Mark Order as Paid (Cashier Only)
- **PATCH** `/api/orders/{id}/mark-paid`
- **Description**: Change order status to paid
- **Authorization**: Cashier role required

### Mark Order as Completed (Cashier Only)
- **PATCH** `/api/orders/{id}/mark-completed`
- **Description**: Change order status to completed
- **Authorization**: Cashier role required

## Error Responses

All error responses follow this format:
```json
{
  "message": "Error message"
}
```

Or for validation errors:
```json
{
  "errors": {
    "field_name": ["Error message 1", "Error message 2"]
  }
}
```

## Guest Order Flow

1. Guest user provides their name and table number
2. Guest user selects menu items to order
3. Order is created with `guest_name` and `table_number`
4. Order status starts as `pending`
5. Cashier marks order as `paid` when payment is received
6. Cashier marks order as `completed` when order is fulfilled