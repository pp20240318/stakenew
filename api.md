# Staking System API Documentation

## Overview

This document outlines the API endpoints for the staking system. The API follows RESTful principles and returns JSON responses. All endpoints are prefixed with `/api/stake`.

## Authentication

Most endpoints require authentication via a token or user ID parameter. The token should be included in the request header as:

```
Authorization: Bearer {token}
```

OR passed as a parameter in the request:

```
?token={token}
```

## Common Response Format

All API responses follow a common format:

```json
{
  "code": 1,        // 1 for success, 0 for failure
  "msg": "string",  // Message describing the result
  "time": 1234567890, // Server timestamp
  "data": {         // Response data (may be an object or array)
    // Response-specific data
  }
}
```

## Error Codes

| Code | Description |
|------|-------------|
| 0    | Request failed |
| 1    | Request successful |
| 401  | Unauthorized - Invalid or expired token |
| 403  | Forbidden - Insufficient permissions |
| 404  | Not found |
| 422  | Validation error |
| 500  | Server error |

## Endpoints

### 1. Stake Plans

#### 1.1 Get All Stake Plans

Get a list of all available staking plans.

- **URL**: `/api/stake/plans`
- **Method**: `GET`
- **Auth Required**: No
- **Query Parameters**:
  - `user_id` (optional): Filter plans available to specific user
  - `sort` (optional): Sort field (e.g., 'duration', 'annual_rate')
  - `order` (optional): Sort order ('asc' or 'desc')
  - `min_amount` (optional): Filter by minimum stake amount
  - `max_amount` (optional): Filter by maximum stake amount
  - `duration` (optional): Filter by staking duration
  - `coin_type` (optional): Filter by coin type

- **Success Response**:
  ```json
  {
    "code": 1,
    "msg": "Success",
    "time": 1234567890,
    "data": {
      "total": 10,
      "per_page": 20,
      "current_page": 1,
      "last_page": 1,
      "data": [
        {
          "id": 1,
          "name": "Basic Staking Plan",
          "description": "Entry level staking plan",
          "coin_type": "USDT",
          "min_amount": 100.00000,
          "max_amount": 1000.00000,
          "duration": 30,
          "annual_rate": 5.00,
          "daily_rate": 0.013699,
          "early_redemption_rate": 2.00,
          "status": 1,
          "level_limit": "[1,2,3]",
          "sort": 1
        }
      ]
    }
  }
  ```

#### 1.2 Get Stake Plan Details

Get detailed information about a specific staking plan.

- **URL**: `/api/stake/planDetail/[:id]`
- **Method**: `GET`
- **Auth Required**: No
- **URL Parameters**:
  - `id`: Plan ID

- **Success Response**:
  ```json
  {
    "code": 1,
    "msg": "Success",
    "time": 1234567890,
    "data": {
      "id": 1,
      "name": "Basic Staking Plan",
      "description": "Entry level staking plan",
      "coin_type": "USDT",
      "min_amount": 100.00000,
      "max_amount": 1000.00000,
      "duration": 30,
      "annual_rate": 5.00,
      "daily_rate": 0.013699,
      "early_redemption_rate": 2.00,
      "status": 1,
      "level_limit": "[1,2,3]",
      "sort": 1,
      "reward_rules": [
        {
          "id": 1,
          "type": "amount",
          "min_value": 100.00000,
          "max_value": 1000.00000,
          "reward_rate": 1.00,
          "description": "100-1000 USDT extra 1% annual reward"
        }
      ]
    }
  }
  ```

### 2. Stake Operations

#### 2.1 Create Stake

Create a new staking position.

- **URL**: `/api/stake/create`
- **Method**: `POST`
- **Auth Required**: Yes
- **Request Body**:
  ```json
  {
    "user_id": 123,
    "plan_id": 1,
    "amount": 500.00000,
    "coin_type": "USDT"
  }
  ```

- **Success Response**:
  ```json
  {
    "code": 1,
    "msg": "Stake created successfully",
    "time": 1234567890,
    "data": {
      "id": 1,
      "user_id": 123,
      "plan_id": 1,
      "order_no": "ST1234567890",
      "amount": 500.00000,
      "coin_type": "USDT",
      "start_time": 1234567890,
      "end_time": 1237159890,
      "duration": 30,
      "status": 0,
      "annual_rate": 5.00,
      "daily_rate": 0.013699,
      "total_reward": 0.00000,
      "received_reward": 0.00000,
      "next_profit_time": 1234654290
    }
  }
  ```

#### 2.2 Redeem Stake

Redeem a staking position (early or on maturity).

- **URL**: `/api/stake/redeem`
- **Method**: `POST`
- **Auth Required**: Yes
- **Request Body**:
  ```json
  {
    "user_id": 123,
    "stake_id": 1
  }
  ```

- **Success Response**:
  ```json
  {
    "code": 1,
    "msg": "Stake redeemed successfully",
    "time": 1234567890,
    "data": {
      "stake_id": 1,
      "redeemed_amount": 495.00000,
      "redemption_fee": 5.00000,
      "status": 2,
      "redemption_time": 1234567890
    }
  }
  ```

#### 2.3 Claim Profit

Claim available profits from stake.

- **URL**: `/api/stake/claimProfit`
- **Method**: `POST`
- **Auth Required**: Yes
- **Request Body**:
  ```json
  {
    "user_id": 123,
    "stake_id": 1
  }
  ```

- **Success Response**:
  ```json
  {
    "code": 1,
    "msg": "Profit claimed successfully",
    "time": 1234567890,
    "data": {
      "claimed_amount": 2.50000,
      "stake_id": 1,
      "profit_ids": [1, 2, 3]
    }
  }
  ```

### 3. User Stake Information

#### 3.1 Get User Stakes

Get all staking positions for a user.

- **URL**: `/api/stake/userStakes`
- **Method**: `GET`
- **Auth Required**: Yes
- **Query Parameters**:
  - `user_id`: User ID
  - `status` (optional): Filter by status (0=active, 1=completed, 2=early redeemed)
  - `coin_type` (optional): Filter by coin type
  - `page` (optional): Page number
  - `limit` (optional): Items per page

- **Success Response**:
  ```json
  {
    "code": 1,
    "msg": "Success",
    "time": 1234567890,
    "data": {
      "total": 5,
      "per_page": 20,
      "current_page": 1,
      "last_page": 1,
      "data": [
        {
          "id": 1,
          "plan_id": 1,
          "plan_name": "Basic Staking Plan",
          "order_no": "ST1234567890",
          "amount": 500.00000,
          "coin_type": "USDT",
          "start_time": 1234567890,
          "end_time": 1237159890,
          "duration": 30,
          "status": 0,
          "annual_rate": 5.00,
          "daily_rate": 0.013699,
          "total_reward": 7.50000,
          "received_reward": 2.50000,
          "next_profit_time": 1234654290,
          "redemption_time": null,
          "redemption_fee": 0.00000
        }
      ]
    }
  }
  ```

#### 3.2 Get Stake Details

Get detailed information about a specific stake.

- **URL**: `/api/stake/details/[:id]`
- **Method**: `GET`
- **Auth Required**: Yes
- **URL Parameters**:
  - `id`: Stake ID
- **Query Parameters**:
  - `user_id`: User ID

- **Success Response**:
  ```json
  {
    "code": 1,
    "msg": "Success",
    "time": 1234567890,
    "data": {
      "stake": {
        "id": 1,
        "user_id": 123,
        "plan_id": 1,
        "plan_name": "Basic Staking Plan",
        "order_no": "ST1234567890",
        "amount": 500.00000,
        "coin_type": "USDT",
        "start_time": 1234567890,
        "end_time": 1237159890,
        "duration": 30,
        "status": 0,
        "annual_rate": 5.00,
        "daily_rate": 0.013699,
        "total_reward": 7.50000,
        "received_reward": 2.50000,
        "next_profit_time": 1234654290,
        "redemption_time": null,
        "redemption_fee": 0.00000
      },
      "profits": [
        {
          "id": 1,
          "amount": 0.83333,
          "coin_type": "USDT",
          "status": 1,
          "receive_time": 1234654290,
          "day_index": 1,
          "profit_date": "2023-01-01"
        },
        {
          "id": 2,
          "amount": 0.83333,
          "coin_type": "USDT",
          "status": 1,
          "receive_time": 1234740690,
          "day_index": 2,
          "profit_date": "2023-01-02"
        }
      ]
    }
  }
  ```

#### 3.3 Get User Stake Summary

Get a summary of user's staking activity.

- **URL**: `/api/stake/userSummary`
- **Method**: `GET`
- **Auth Required**: Yes
- **Query Parameters**:
  - `user_id`: User ID
  - `coin_type` (optional): Filter by coin type

- **Success Response**:
  ```json
  {
    "code": 1,
    "msg": "Success",
    "time": 1234567890,
    "data": {
      "total_staked": 1500.00000,
      "active_stakes": 3,
      "total_earned": 50.00000,
      "unclaimed_profit": 5.00000,
      "user_level": {
        "level_id": 2,
        "level_name": "Silver",
        "reward_boost": 1.00,
        "total_staked": 1500.00000,
        "expire_time": 1237159890
      }
    }
  }
  ```

#### 3.4 Get Profit History

Get profit history for a user.

- **URL**: `/api/stake/profitHistory`
- **Method**: `GET`
- **Auth Required**: Yes
- **Query Parameters**:
  - `user_id`: User ID
  - `stake_id` (optional): Filter by stake ID
  - `status` (optional): Filter by status (0=unclaimed, 1=claimed)
  - `page` (optional): Page number
  - `limit` (optional): Items per page

- **Success Response**:
  ```json
  {
    "code": 1,
    "msg": "Success",
    "time": 1234567890,
    "data": {
      "total": 30,
      "per_page": 20,
      "current_page": 1,
      "last_page": 2,
      "data": [
        {
          "id": 1,
          "stake_record_id": 1,
          "plan_id": 1,
          "plan_name": "Basic Staking Plan",
          "amount": 0.83333,
          "coin_type": "USDT",
          "status": 1,
          "receive_time": 1234654290,
          "day_index": 1,
          "profit_date": "2023-01-01"
        }
      ]
    }
  }
  ```

### 4. User Level System

#### 4.1 Get All Levels

Get all available stake user levels.

- **URL**: `/api/stake/levels`
- **Method**: `GET`
- **Auth Required**: No

- **Success Response**:
  ```json
  {
    "code": 1,
    "msg": "Success",
    "time": 1234567890,
    "data": [
      {
        "id": 1,
        "name": "Bronze",
        "description": "Entry level",
        "level": 1,
        "required_amount": 100.00000,
        "required_duration": 30,
        "reward_boost": 0.00,
        "icon": "https://example.com/bronze.png"
      },
      {
        "id": 2,
        "name": "Silver",
        "description": "Mid level",
        "level": 2,
        "required_amount": 1000.00000,
        "required_duration": 90,
        "reward_boost": 1.00,
        "icon": "https://example.com/silver.png"
      }
    ]
  }
  ```

#### 4.2 Get User Level

Get user's current stake level.

- **URL**: `/api/stake/userLevel`
- **Method**: `GET`
- **Auth Required**: Yes
- **Query Parameters**:
  - `user_id`: User ID

- **Success Response**:
  ```json
  {
    "code": 1,
    "msg": "Success",
    "time": 1234567890,
    "data": {
      "user_level": {
        "level_id": 2,
        "level_name": "Silver",
        "level_description": "Mid level",
        "level": 2,
        "reward_boost": 1.00,
        "total_staked": 1500.00000,
        "max_duration": 90,
        "expire_time": 1237159890,
        "status": 1,
        "icon": "https://example.com/silver.png"
      },
      "next_level": {
        "id": 3,
        "name": "Gold",
        "level": 3,
        "required_amount": 5000.00000,
        "required_duration": 180,
        "reward_boost": 2.00,
        "progress": 30,
        "amount_needed": 3500.00000
      }
    }
  }
  ```

### 5. Referral System

#### 5.1 Get Referral Stats

Get user's referral statistics.

- **URL**: `/api/stake/referralStats`
- **Method**: `GET`
- **Auth Required**: Yes
- **Query Parameters**:
  - `user_id`: User ID

- **Success Response**:
  ```json
  {
    "code": 1,
    "msg": "Success",
    "time": 1234567890,
    "data": {
      "total_referrals": 10,
      "level1_referrals": 8,
      "level2_referrals": 2,
      "total_reward": 150.00000,
      "level1_reward": 120.00000,
      "level2_reward": 30.00000,
      "pending_reward": 5.00000
    }
  }
  ```

#### 5.2 Get Referral Rewards

Get detailed referral rewards.

- **URL**: `/api/stake/referralRewards`
- **Method**: `GET`
- **Auth Required**: Yes
- **Query Parameters**:
  - `user_id`: User ID
  - `status` (optional): Filter by status (0=pending, 1=paid)
  - `page` (optional): Page number
  - `limit` (optional): Items per page

- **Success Response**:
  ```json
  {
    "code": 1,
    "msg": "Success",
    "time": 1234567890,
    "data": {
      "total": 15,
      "per_page": 10,
      "current_page": 1,
      "last_page": 2,
      "data": [
        {
          "id": 1,
          "referral_id": 456,
          "referral_username": "user456",
          "stake_record_id": 5,
          "level": 1,
          "amount": 25.00000,
          "coin_type": "USDT",
          "rate": 5.00,
          "status": 1,
          "create_time": 1234567890,
          "update_time": 1234567990
        }
      ]
    }
  }
  ```

### 6. Wallet Operations

#### 6.1 Get Wallet Transactions

Get wallet transaction history for staking.

- **URL**: `/api/stake/walletRecords`
- **Method**: `GET`
- **Auth Required**: Yes
- **Query Parameters**:
  - `user_id`: User ID
  - `type` (optional): Transaction type (stake, profit, referral, redemption)
  - `coin_type` (optional): Filter by coin type
  - `page` (optional): Page number
  - `limit` (optional): Items per page

- **Success Response**:
  ```json
  {
    "code": 1,
    "msg": "Success",
    "time": 1234567890,
    "data": {
      "total": 20,
      "per_page": 10,
      "current_page": 1,
      "last_page": 2,
      "data": [
        {
          "id": 1,
          "type": "stake",
          "amount": -500.00000,
          "coin_type": "USDT",
          "related_id": 1,
          "description": "Staked 500 USDT in Basic Staking Plan",
          "create_time": 1234567890
        },
        {
          "id": 2,
          "type": "profit",
          "amount": 0.83333,
          "coin_type": "USDT",
          "related_id": 1,
          "description": "Staking profit for day 1",
          "create_time": 1234654290
        }
      ]
    }
  }
  ```

### 7. Activities and Promotions

#### 7.1 Get Staking Activities

Get active staking promotional activities.

- **URL**: `/api/stake/activities`
- **Method**: `GET`
- **Auth Required**: No
- **Query Parameters**:
  - `status` (optional): Filter by status (0=upcoming, 1=active, 2=ended)

- **Success Response**:
  ```json
  {
    "code": 1,
    "msg": "Success",
    "time": 1234567890,
    "data": [
      {
        "id": 1,
        "title": "Summer Staking Bonus",
        "description": "Get extra 2% APY during summer",
        "start_time": 1234567890,
        "end_time": 1237159890,
        "bonus_rate": 2.00,
        "min_stake_amount": 100.00000,
        "min_stake_duration": 30,
        "status": 1
      }
    ]
  }
  ```

### 8. System Configuration

#### 8.1 Get Staking Configuration

Get system configuration for staking.

- **URL**: `/api/stake/config`
- **Method**: `GET`
- **Auth Required**: No

- **Success Response**:
  ```json
  {
    "code": 1,
    "msg": "Success",
    "time": 1234567890,
    "data": {
      "level1_referral_rate": 5.00,
      "level2_referral_rate": 2.00,
      "min_stake_amount": 10.00000,
      "stake_reward_time": "00:00:00",
      "redemption_fee_rate": 2.00
    }
  }
  ```

## Postman Collection

```json
{
  "info": {
    "name": "Staking System API",
    "description": "API collection for the staking system",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Stake Plans",
      "item": [
        {
          "name": "Get All Stake Plans",
          "request": {
            "method": "GET",
            "url": {
              "raw": "{{base_url}}/api/stake/plans?user_id=123&sort=duration&order=asc",
              "host": ["{{base_url}}"],
              "path": ["api", "stake", "plans"],
              "query": [
                {"key": "user_id", "value": "123"},
                {"key": "sort", "value": "duration"},
                {"key": "order", "value": "asc"}
              ]
            }
          }
        },
        {
          "name": "Get Stake Plan Details",
          "request": {
            "method": "GET",
            "url": {
              "raw": "{{base_url}}/api/stake/planDetail/1",
              "host": ["{{base_url}}"],
              "path": ["api", "stake", "planDetail", "1"]
            }
          }
        }
      ]
    },
    {
      "name": "Stake Operations",
      "item": [
        {
          "name": "Create Stake",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/api/stake/create",
              "host": ["{{base_url}}"],
              "path": ["api", "stake", "create"]
            },
            "body": {
              "mode": "raw",
              "raw": "{\n  \"user_id\": 123,\n  \"plan_id\": 1,\n  \"amount\": 500.00000,\n  \"coin_type\": \"USDT\"\n}"
            }
          }
        },
        {
          "name": "Redeem Stake",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/api/stake/redeem",
              "host": ["{{base_url}}"],
              "path": ["api", "stake", "redeem"]
            },
            "body": {
              "mode": "raw",
              "raw": "{\n  \"user_id\": 123,\n  \"stake_id\": 1\n}"
            }
          }
        },
        {
          "name": "Claim Profit",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              },
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/api/stake/claimProfit",
              "host": ["{{base_url}}"],
              "path": ["api", "stake", "claimProfit"]
            },
            "body": {
              "mode": "raw",
              "raw": "{\n  \"user_id\": 123,\n  \"stake_id\": 1\n}"
            }
          }
        }
      ]
    },
    {
      "name": "User Stake Information",
      "item": [
        {
          "name": "Get User Stakes",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/api/stake/userStakes?user_id=123&status=0&page=1&limit=20",
              "host": ["{{base_url}}"],
              "path": ["api", "stake", "userStakes"],
              "query": [
                {"key": "user_id", "value": "123"},
                {"key": "status", "value": "0"},
                {"key": "page", "value": "1"},
                {"key": "limit", "value": "20"}
              ]
            }
          }
        },
        {
          "name": "Get Stake Details",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/api/stake/details/1?user_id=123",
              "host": ["{{base_url}}"],
              "path": ["api", "stake", "details", "1"],
              "query": [
                {"key": "user_id", "value": "123"}
              ]
            }
          }
        },
        {
          "name": "Get User Stake Summary",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/api/stake/userSummary?user_id=123",
              "host": ["{{base_url}}"],
              "path": ["api", "stake", "userSummary"],
              "query": [
                {"key": "user_id", "value": "123"}
              ]
            }
          }
        },
        {
          "name": "Get Profit History",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/api/stake/profitHistory?user_id=123&stake_id=1&status=1&page=1&limit=20",
              "host": ["{{base_url}}"],
              "path": ["api", "stake", "profitHistory"],
              "query": [
                {"key": "user_id", "value": "123"},
                {"key": "stake_id", "value": "1"},
                {"key": "status", "value": "1"},
                {"key": "page", "value": "1"},
                {"key": "limit", "value": "20"}
              ]
            }
          }
        }
      ]
    },
    {
      "name": "User Level System",
      "item": [
        {
          "name": "Get All Levels",
          "request": {
            "method": "GET",
            "url": {
              "raw": "{{base_url}}/api/stake/levels",
              "host": ["{{base_url}}"],
              "path": ["api", "stake", "levels"]
            }
          }
        },
        {
          "name": "Get User Level",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/api/stake/userLevel?user_id=123",
              "host": ["{{base_url}}"],
              "path": ["api", "stake", "userLevel"],
              "query": [
                {"key": "user_id", "value": "123"}
              ]
            }
          }
        }
      ]
    },
    {
      "name": "Referral System",
      "item": [
        {
          "name": "Get Referral Stats",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/api/stake/referralStats?user_id=123",
              "host": ["{{base_url}}"],
              "path": ["api", "stake", "referralStats"],
              "query": [
                {"key": "user_id", "value": "123"}
              ]
            }
          }
        },
        {
          "name": "Get Referral Rewards",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/api/stake/referralRewards?user_id=123&status=1&page=1&limit=10",
              "host": ["{{base_url}}"],
              "path": ["api", "stake", "referralRewards"],
              "query": [
                {"key": "user_id", "value": "123"},
                {"key": "status", "value": "1"},
                {"key": "page", "value": "1"},
                {"key": "limit", "value": "10"}
              ]
            }
          }
        }
      ]
    },
    {
      "name": "Wallet Operations",
      "item": [
        {
          "name": "Get Wallet Transactions",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "Authorization",
                "value": "Bearer {{token}}"
              }
            ],
            "url": {
              "raw": "{{base_url}}/api/stake/walletRecords?user_id=123&type=profit&page=1&limit=10",
              "host": ["{{base_url}}"],
              "path": ["api", "stake", "walletRecords"],
              "query": [
                {"key": "user_id", "value": "123"},
                {"key": "type", "value": "profit"},
                {"key": "page", "value": "1"},
                {"key": "limit", "value": "10"}
              ]
            }
          }
        }
      ]
    },
    {
      "name": "Activities and Promotions",
      "item": [
        {
          "name": "Get Staking Activities",
          "request": {
            "method": "GET",
            "url": {
              "raw": "{{base_url}}/api/stake/activities?status=1",
              "host": ["{{base_url}}"],
              "path": ["api", "stake", "activities"],
              "query": [
                {"key": "status", "value": "1"}
              ]
            }
          }
        }
      ]
    },
    {
      "name": "System Configuration",
      "item": [
        {
          "name": "Get Staking Configuration",
          "request": {
            "method": "GET",
            "url": {
              "raw": "{{base_url}}/api/stake/config",
              "host": ["{{base_url}}"],
              "path": ["api", "stake", "config"]
            }
          }
        }
      ]
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost:8000"
    },
    {
      "key": "token",
      "value": "your_token_here"
    }
  ]
}
``` 