// Mock API implementation for stake system
// This provides test data when API is not available

export function useMockApi() {
    // Return mock implementation of all API functions
    return {
        // Stake Plans
        getStakePlans: () => {
            return Promise.resolve({
                code: 0,
                msg: 'success',
                data: {
                    data: [
                        {
                            id: 1,
                            name: 'Basic Stake',
                            description: 'A basic staking plan for beginners',
                            annual_rate: 5.5,
                            daily_rate: 0.015,
                            duration: 30,
                            min_amount: 100,
                            max_amount: 10000,
                            coin_type: 'USDT',
                            early_redemption_rate: 2.5,
                            status: 1,
                            create_time: '2023-01-01 12:00:00'
                        },
                        {
                            id: 2,
                            name: 'Premium Stake',
                            description: 'Premium staking plan with higher returns',
                            annual_rate: 8.0,
                            daily_rate: 0.022,
                            duration: 90,
                            min_amount: 1000,
                            max_amount: 50000,
                            coin_type: 'USDT',
                            early_redemption_rate: 3.0,
                            status: 1,
                            create_time: '2023-01-15 10:30:00'
                        },
                        {
                            id: 3,
                            name: 'Elite Stake',
                            description: 'Elite staking plan with highest returns',
                            annual_rate: 12.0,
                            daily_rate: 0.033,
                            duration: 180,
                            min_amount: 5000,
                            max_amount: 100000,
                            coin_type: 'USDT',
                            early_redemption_rate: 5.0,
                            status: 1,
                            create_time: '2023-02-01 09:15:00'
                        }
                    ],
                    total: 3
                }
            });
        },
        
        getStakePlanDetail: (id: number) => {
            const plans: Record<number, any> = {
                1: {
                    id: 1,
                    name: 'Basic Stake',
                    description: 'A basic staking plan for beginners',
                    annual_rate: 5.5,
                    daily_rate: 0.015,
                    duration: 30,
                    min_amount: 100,
                    max_amount: 10000,
                    coin_type: 'USDT',
                    early_redemption_rate: 2.5,
                    status: 1,
                    create_time: '2023-01-01 12:00:00',
                    terms_conditions: 'Standard terms and conditions apply',
                    profit_distribution: 'Daily profit distribution'
                },
                2: {
                    id: 2,
                    name: 'Premium Stake',
                    description: 'Premium staking plan with higher returns',
                    annual_rate: 8.0,
                    daily_rate: 0.022,
                    duration: 90,
                    min_amount: 1000,
                    max_amount: 50000,
                    coin_type: 'USDT',
                    early_redemption_rate: 3.0,
                    status: 1,
                    create_time: '2023-01-15 10:30:00',
                    terms_conditions: 'Premium terms and conditions apply',
                    profit_distribution: 'Daily profit distribution with bonuses'
                },
                3: {
                    id: 3,
                    name: 'Elite Stake',
                    description: 'Elite staking plan with highest returns',
                    annual_rate: 12.0,
                    daily_rate: 0.033,
                    duration: 180,
                    min_amount: 5000,
                    max_amount: 100000,
                    coin_type: 'USDT',
                    early_redemption_rate: 5.0,
                    status: 1,
                    create_time: '2023-02-01 09:15:00',
                    terms_conditions: 'Elite terms and conditions apply',
                    profit_distribution: 'Daily profit distribution with premium bonuses'
                }
            };
            
            return Promise.resolve({
                code: 0,
                msg: 'success',
                data: plans[id] || {}
            });
        },
        
        // Stake Operations
        createStake: (data: any) => {
            return Promise.resolve({
                code: 0,
                msg: 'Stake created successfully',
                data: {
                    id: Math.floor(Math.random() * 1000) + 1,
                    plan_id: data.plan_id,
                    user_id: data.user_id,
                    amount: data.amount,
                    start_time: new Date().toISOString(),
                    end_time: new Date(Date.now() + (data.duration || 30) * 86400000).toISOString(),
                    status: 1,
                    current_profit: 0,
                    total_profit: 0
                }
            });
        },
        
        redeemStake: (data: any) => {
            return Promise.resolve({
                code: 0,
                msg: 'Stake redeemed successfully',
                data: {
                    id: data.stake_id,
                    status: 2, // Redeemed
                    redemption_amount: data.amount,
                    redemption_fee: data.early ? data.amount * 0.05 : 0,
                    net_amount: data.early ? data.amount * 0.95 : data.amount,
                    redemption_time: new Date().toISOString()
                }
            });
        },
        
        claimProfit: (data: any) => {
            return Promise.resolve({
                code: 0,
                msg: 'Profit claimed successfully',
                data: {
                    stake_id: data.stake_id,
                    claimed_amount: data.amount,
                    claim_time: new Date().toISOString(),
                    remaining_profit: 0
                }
            });
        },
        
        // User Stake Information
        getUserStakes: (params: any) => {
            const mockStakes = [
                {
                    id: 101,
                    plan_id: 1,
                    plan_name: 'Basic Stake',
                    user_id: params.user_id || 1,
                    amount: 500,
                    start_time: '2023-05-01 10:00:00',
                    end_time: '2023-05-31 10:00:00',
                    status: 1, // Active
                    current_profit: 7.5,
                    total_profit: 7.5,
                    daily_profit: 0.075,
                    coin_type: 'USDT'
                },
                {
                    id: 102,
                    plan_id: 2,
                    plan_name: 'Premium Stake',
                    user_id: params.user_id || 1,
                    amount: 2000,
                    start_time: '2023-04-15 14:30:00',
                    end_time: '2023-07-14 14:30:00',
                    status: 1, // Active
                    current_profit: 44.0,
                    total_profit: 44.0,
                    daily_profit: 0.44,
                    coin_type: 'USDT'
                }
            ];
            
            return Promise.resolve({
                code: 0,
                msg: 'success',
                data: {
                    data: mockStakes,
                    total: mockStakes.length
                }
            });
        },
        
        getStakeDetails: (id: number, userId: number) => {
            const mockStakeDetails = {
                id: id,
                plan_id: id % 3 + 1,
                plan_name: ['Basic Stake', 'Premium Stake', 'Elite Stake'][id % 3],
                user_id: userId,
                amount: 1000 * (id % 5 + 1),
                start_time: '2023-05-01 10:00:00',
                end_time: '2023-06-30 10:00:00',
                status: 1, // Active
                current_profit: 15 * (id % 5 + 1),
                total_profit: 15 * (id % 5 + 1),
                daily_profit: 0.5 * (id % 5 + 1),
                coin_type: 'USDT',
                profit_history: [
                    {
                        date: '2023-05-02',
                        amount: 0.5 * (id % 5 + 1),
                        status: 'credited'
                    },
                    {
                        date: '2023-05-03',
                        amount: 0.5 * (id % 5 + 1),
                        status: 'credited'
                    },
                    {
                        date: '2023-05-04',
                        amount: 0.5 * (id % 5 + 1),
                        status: 'credited'
                    }
                ]
            };
            
            return Promise.resolve({
                code: 0,
                msg: 'success',
                data: mockStakeDetails
            });
        },
        
        getUserSummary: (userId: number) => {
            return Promise.resolve({
                code: 0,
                msg: 'success',
                data: {
                    total_staked: 2500,
                    active_stakes: 2,
                    total_profit: 51.5,
                    current_profit: 51.5,
                    user_level: 2,
                    level_name: 'Silver',
                    coin_type: 'USDT'
                }
            });
        },
        
        getProfitHistory: (params: any) => {
            const mockProfitHistory = [
                {
                    id: 1001,
                    stake_id: 101,
                    plan_name: 'Basic Stake',
                    amount: 0.075,
                    date: '2023-05-02',
                    status: 'credited',
                    coin_type: 'USDT'
                },
                {
                    id: 1002,
                    stake_id: 101,
                    plan_name: 'Basic Stake',
                    amount: 0.075,
                    date: '2023-05-03',
                    status: 'credited',
                    coin_type: 'USDT'
                },
                {
                    id: 1003,
                    stake_id: 102,
                    plan_name: 'Premium Stake',
                    amount: 0.44,
                    date: '2023-05-02',
                    status: 'credited',
                    coin_type: 'USDT'
                },
                {
                    id: 1004,
                    stake_id: 102,
                    plan_name: 'Premium Stake',
                    amount: 0.44,
                    date: '2023-05-03',
                    status: 'credited',
                    coin_type: 'USDT'
                }
            ];
            
            return Promise.resolve({
                code: 0,
                msg: 'success',
                data: {
                    data: mockProfitHistory,
                    total: mockProfitHistory.length
                }
            });
        },
        
        // User Level System
        getStakeLevels: () => {
            return Promise.resolve({
                code: 0,
                msg: 'success',
                data: [
                    {
                        id: 1,
                        name: 'Bronze',
                        min_stake: 0,
                        max_stake: 999,
                        benefits: 'Basic staking benefits',
                        referral_bonus: 1.0
                    },
                    {
                        id: 2,
                        name: 'Silver',
                        min_stake: 1000,
                        max_stake: 4999,
                        benefits: 'Silver staking benefits, 2% bonus on profits',
                        referral_bonus: 1.5
                    },
                    {
                        id: 3,
                        name: 'Gold',
                        min_stake: 5000,
                        max_stake: 19999,
                        benefits: 'Gold staking benefits, 5% bonus on profits',
                        referral_bonus: 2.0
                    },
                    {
                        id: 4,
                        name: 'Platinum',
                        min_stake: 20000,
                        max_stake: 99999,
                        benefits: 'Platinum staking benefits, 10% bonus on profits',
                        referral_bonus: 3.0
                    },
                    {
                        id: 5,
                        name: 'Diamond',
                        min_stake: 100000,
                        max_stake: null,
                        benefits: 'Diamond staking benefits, 15% bonus on profits',
                        referral_bonus: 5.0
                    }
                ]
            });
        },
        
        getUserLevel: (userId: number) => {
            return Promise.resolve({
                code: 0,
                msg: 'success',
                data: {
                    user_id: userId,
                    level_id: 2,
                    level_name: 'Silver',
                    current_stake: 2500,
                    next_level: 'Gold',
                    next_level_requirement: 5000,
                    progress_percentage: 50
                }
            });
        },
        
        // Referral System
        getReferralInfo: (userId: number) => {
            return Promise.resolve({
                code: 0,
                msg: 'success',
                data: {
                    user_id: userId,
                    referral_code: 'ABC123',
                    total_referrals: 5,
                    active_referrals: 3,
                    total_earnings: 75.5,
                    pending_earnings: 12.5,
                    referral_link: 'https://example.com/ref/ABC123'
                }
            });
        },
        
        getReferralList: (params: any) => {
            const mockReferrals = [
                {
                    id: 1,
                    user_id: params.user_id,
                    referred_user_id: 101,
                    referred_username: 'user101',
                    signup_date: '2023-04-10',
                    status: 'active',
                    total_stake: 1500,
                    commission_earned: 30.0
                },
                {
                    id: 2,
                    user_id: params.user_id,
                    referred_user_id: 102,
                    referred_username: 'user102',
                    signup_date: '2023-04-15',
                    status: 'active',
                    total_stake: 2000,
                    commission_earned: 40.0
                },
                {
                    id: 3,
                    user_id: params.user_id,
                    referred_user_id: 103,
                    referred_username: 'user103',
                    signup_date: '2023-04-20',
                    status: 'active',
                    total_stake: 500,
                    commission_earned: 5.5
                },
                {
                    id: 4,
                    user_id: params.user_id,
                    referred_user_id: 104,
                    referred_username: 'user104',
                    signup_date: '2023-04-25',
                    status: 'inactive',
                    total_stake: 0,
                    commission_earned: 0
                },
                {
                    id: 5,
                    user_id: params.user_id,
                    referred_user_id: 105,
                    referred_username: 'user105',
                    signup_date: '2023-04-30',
                    status: 'inactive',
                    total_stake: 0,
                    commission_earned: 0
                }
            ];
            
            return Promise.resolve({
                code: 0,
                msg: 'success',
                data: {
                    data: mockReferrals,
                    total: mockReferrals.length
                }
            });
        },
        
        getReferralRewards: (params: any) => {
            const mockRewards = [
                {
                    id: 201,
                    user_id: params.user_id,
                    referred_user_id: 101,
                    referred_username: 'user101',
                    amount: 10.0,
                    stake_id: 1001,
                    date: '2023-05-01',
                    status: 'paid',
                    coin_type: 'USDT'
                },
                {
                    id: 202,
                    user_id: params.user_id,
                    referred_user_id: 102,
                    referred_username: 'user102',
                    amount: 15.0,
                    stake_id: 1002,
                    date: '2023-05-05',
                    status: 'paid',
                    coin_type: 'USDT'
                },
                {
                    id: 203,
                    user_id: params.user_id,
                    referred_user_id: 101,
                    referred_username: 'user101',
                    amount: 5.0,
                    stake_id: 1003,
                    date: '2023-05-10',
                    status: 'paid',
                    coin_type: 'USDT'
                },
                {
                    id: 204,
                    user_id: params.user_id,
                    referred_user_id: 102,
                    referred_username: 'user102',
                    amount: 8.0,
                    stake_id: 1004,
                    date: '2023-05-15',
                    status: 'pending',
                    coin_type: 'USDT'
                },
                {
                    id: 205,
                    user_id: params.user_id,
                    referred_user_id: 103,
                    referred_username: 'user103',
                    amount: 4.5,
                    stake_id: 1005,
                    date: '2023-05-20',
                    status: 'pending',
                    coin_type: 'USDT'
                }
            ];
            
            return Promise.resolve({
                code: 0,
                msg: 'success',
                data: {
                    data: mockRewards,
                    total: mockRewards.length
                }
            });
        },
        
        // Wallet Operations
        getWalletSummary: (userId: number) => {
            return Promise.resolve({
                code: 0,
                msg: 'success',
                data: {
                    user_id: userId,
                    balance: 1523.75,
                    pending_deposits: 1,
                    pending_withdrawals: 0,
                    total_deposits: 2000,
                    total_withdrawals: 500,
                    deposit_address: '0x1234567890abcdef1234567890abcdef12345678',
                    coin_type: 'USDT'
                }
            });
        },
        
        getWalletTransactions: (params: any) => {
            const mockTransactions = [
                {
                    id: 301,
                    user_id: params.user_id,
                    type: 'deposit',
                    amount: 1000,
                    status: 'completed',
                    txid: '0xabcdef1234567890abcdef1234567890abcdef1234567890',
                    created_at: '2023-04-15 10:30:00',
                    updated_at: '2023-04-15 11:00:00',
                    coin_type: 'USDT'
                },
                {
                    id: 302,
                    user_id: params.user_id,
                    type: 'withdrawal',
                    amount: 250,
                    status: 'completed',
                    txid: '0x1234567890abcdef1234567890abcdef1234567890abcdef',
                    created_at: '2023-04-20 14:15:00',
                    updated_at: '2023-04-20 14:45:00',
                    coin_type: 'USDT'
                },
                {
                    id: 303,
                    user_id: params.user_id,
                    type: 'stake_profit',
                    amount: 23.75,
                    status: 'completed',
                    txid: null,
                    created_at: '2023-05-01 00:00:00',
                    updated_at: '2023-05-01 00:00:00',
                    coin_type: 'USDT'
                },
                {
                    id: 304,
                    user_id: params.user_id,
                    type: 'deposit',
                    amount: 1000,
                    status: 'pending',
                    txid: '0xfedcba0987654321fedcba0987654321fedcba0987654321',
                    created_at: '2023-05-10 09:45:00',
                    updated_at: '2023-05-10 09:45:00',
                    coin_type: 'USDT'
                },
                {
                    id: 305,
                    user_id: params.user_id,
                    type: 'withdrawal',
                    amount: 250,
                    status: 'completed',
                    txid: '0x0987654321fedcba0987654321fedcba0987654321fedcba',
                    created_at: '2023-05-05 16:30:00',
                    updated_at: '2023-05-05 17:00:00',
                    coin_type: 'USDT'
                }
            ];
            
            return Promise.resolve({
                code: 0,
                msg: 'success',
                data: {
                    data: mockTransactions,
                    total: mockTransactions.length
                }
            });
        },
        
        getDepositInfo: (userId: number) => {
            return Promise.resolve({
                code: 0,
                msg: 'success',
                data: {
                    user_id: userId,
                    deposit_address: '0x1234567890abcdef1234567890abcdef12345678',
                    min_deposit: 50,
                    confirmations_required: 6,
                    deposit_note: 'Please wait for at least 6 confirmations before your deposit is credited.',
                    coin_type: 'USDT',
                    network: 'TRC20'
                }
            });
        },
        
        notifyDeposit: (data: any) => {
            return Promise.resolve({
                code: 0,
                msg: 'Deposit notification received. We will verify and credit your account shortly.',
                data: {
                    id: Math.floor(Math.random() * 1000) + 1,
                    user_id: data.user_id,
                    amount: data.amount,
                    txid: data.txid,
                    status: 'pending',
                    created_at: new Date().toISOString()
                }
            });
        },
        
        withdrawFunds: (data: any) => {
            return Promise.resolve({
                code: 0,
                msg: 'Withdrawal request submitted successfully.',
                data: {
                    id: Math.floor(Math.random() * 1000) + 1,
                    user_id: data.user_id,
                    amount: data.amount,
                    address: data.address,
                    status: 'pending',
                    created_at: new Date().toISOString()
                }
            });
        },
        
        cancelWithdrawal: (data: any) => {
            return Promise.resolve({
                code: 0,
                msg: 'Withdrawal request cancelled successfully.',
                data: {
                    id: data.id,
                    status: 'cancelled',
                    updated_at: new Date().toISOString()
                }
            });
        },
        
        // System Configuration
        getConfig: () => {
            return Promise.resolve({
                code: 0,
                msg: 'success',
                data: {
                    min_stake_amount: 50,
                    max_stake_amount: 1000000,
                    min_withdrawal: 100,
                    withdrawal_fee: 1.0,
                    referral_bonus_percentage: 2.0,
                    supported_currencies: ['USDT', 'BTC', 'ETH'],
                    stake_profit_distribution_time: '00:00 UTC',
                    maintenance_mode: false,
                    support_email: 'support@example.com'
                }
            });
        },
        
        // Market Data
        getMarketData: () => {
            return Promise.resolve({
                code: 0,
                msg: 'success',
                data: {
                    last_updated: new Date().toISOString(),
                    data: [
                        {
                            symbol: 'BTC/USDT',
                            price: 45245.78,
                            change_24h: 2.35,
                            volume_24h: 28456789.45,
                            market_cap: 876543210987.65,
                            high_24h: 46123.45,
                            low_24h: 44789.56
                        },
                        {
                            symbol: 'ETH/USDT',
                            price: 2356.89,
                            change_24h: 1.56,
                            volume_24h: 12345678.90,
                            market_cap: 298765432109.87,
                            high_24h: 2399.45,
                            low_24h: 2310.67
                        },
                        {
                            symbol: 'BNB/USDT',
                            price: 456.78,
                            change_24h: -0.75,
                            volume_24h: 3456789.12,
                            market_cap: 76543210987.65,
                            high_24h: 464.56,
                            low_24h: 452.34
                        },
                        {
                            symbol: 'XRP/USDT',
                            price: 0.5678,
                            change_24h: 3.45,
                            volume_24h: 2345678.90,
                            market_cap: 28765432109.87,
                            high_24h: 0.5789,
                            low_24h: 0.5456
                        },
                        {
                            symbol: 'SOL/USDT',
                            price: 123.45,
                            change_24h: 5.67,
                            volume_24h: 3456789.12,
                            market_cap: 47654321098.76,
                            high_24h: 128.90,
                            low_24h: 119.87
                        }
                    ]
                }
            });
        },
        
        getMarketChart: (symbol: string, timeframe: string) => {
            // Generate random chart data based on the symbol and timeframe
            const generateChartData = (symbol: string, timeframe: string) => {
                const periods = timeframe === '1d' ? 24 : timeframe === '1w' ? 7 : 30;
                const data = [];
                let basePrice = symbol.includes('BTC') ? 45000 : 
                               symbol.includes('ETH') ? 2300 : 
                               symbol.includes('BNB') ? 450 : 
                               symbol.includes('XRP') ? 0.55 : 
                               symbol.includes('SOL') ? 120 : 100;
                
                const now = new Date();
                const timeStep = timeframe === '1d' ? 3600000 : // 1 hour in ms
                                timeframe === '1w' ? 86400000 : // 1 day in ms
                                2592000000 / 30; // approx 1 day in ms for 1 month
                
                for (let i = 0; i < periods; i++) {
                    // Add some randomness to the price
                    const randomFactor = 1 + (Math.random() * 0.1 - 0.05); // ±5%
                    basePrice = basePrice * randomFactor;
                    
                    data.push({
                        time: new Date(now.getTime() - (periods - i) * timeStep).toISOString(),
                        price: parseFloat(basePrice.toFixed(2)),
                        volume: Math.floor(Math.random() * 1000000) + 500000
                    });
                }
                
                return data;
            };
            
            return Promise.resolve({
                code: 0,
                msg: 'success',
                data: {
                    symbol: symbol,
                    timeframe: timeframe,
                    data: generateChartData(symbol, timeframe)
                }
            });
        },
        
        getMarketTrending: () => {
            return Promise.resolve({
                code: 0,
                msg: 'success',
                data: [
                    {
                        symbol: 'SOL/USDT',
                        price: 123.45,
                        change_24h: 5.67,
                        trend_score: 95
                    },
                    {
                        symbol: 'AVAX/USDT',
                        price: 34.56,
                        change_24h: 7.89,
                        trend_score: 92
                    },
                    {
                        symbol: 'DOGE/USDT',
                        price: 0.1234,
                        change_24h: 4.56,
                        trend_score: 88
                    },
                    {
                        symbol: 'LINK/USDT',
                        price: 16.78,
                        change_24h: 3.45,
                        trend_score: 85
                    },
                    {
                        symbol: 'DOT/USDT',
                        price: 8.90,
                        change_24h: 2.34,
                        trend_score: 80
                    }
                ]
            });
        },
        
        getActivities: (status?: number) => {
            const activities = [
                {
                    id: 1,
                    title: 'Double Staking Rewards',
                    description: 'Stake now and earn double rewards for the next 7 days!',
                    start_date: '2023-05-01',
                    end_date: '2023-05-07',
                    status: 1, // Active
                    rules: 'Minimum stake amount: 500 USDT. Double rewards applied automatically.',
                    banner_url: 'https://example.com/banners/double_rewards.jpg'
                },
                {
                    id: 2,
                    title: 'Referral Bonus Boost',
                    description: 'Get 5% commission on your referrals\' stakes instead of the regular 2%!',
                    start_date: '2023-05-10',
                    end_date: '2023-05-20',
                    status: 1, // Active
                    rules: 'Valid for all referrals who stake during the promotion period.',
                    banner_url: 'https://example.com/banners/referral_boost.jpg'
                },
                {
                    id: 3,
                    title: 'No Early Redemption Fee',
                    description: 'Redeem your stakes early with no penalty fee!',
                    start_date: '2023-04-15',
                    end_date: '2023-04-30',
                    status: 2, // Ended
                    rules: 'Valid for stakes created before April 15, 2023.',
                    banner_url: 'https://example.com/banners/no_redemption_fee.jpg'
                }
            ];
            
            let filteredActivities = activities;
            if (status !== undefined) {
                filteredActivities = activities.filter(a => a.status === status);
            }
            
            return Promise.resolve({
                code: 0,
                msg: 'success',
                data: {
                    data: filteredActivities,
                    total: filteredActivities.length
                }
            });
        }
    };
} 