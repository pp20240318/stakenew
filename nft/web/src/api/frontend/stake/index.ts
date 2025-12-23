import createAxios from '/@/utils/axios'
import { useMockApi } from './mockApi'

// Toggle this to use mock API or real API
const USE_MOCK_API = true
const mockApi = USE_MOCK_API ? useMockApi() : null

export const stakeUrl = '/api/stake/'

// Stake Plans APIs
export function getStakePlans(params: any = {}) {
    if (mockApi) return mockApi.getStakePlans(params)
    
    return createAxios({
        url: stakeUrl + 'plans',
        method: 'get',
        params: params,
    })
}

export function getStakePlanDetail(id: number) {
    if (mockApi) return mockApi.getStakePlanDetail(id)
    
    return createAxios({
        url: stakeUrl + 'planDetail/' + id,
        method: 'get',
    })
}

// Stake Operations APIs
export function createStake(data: anyObj) {
    if (mockApi) return mockApi.createStake(data)
    
    return createAxios({
        url: stakeUrl + 'create',
        method: 'post',
        data: data,
    }, {
        showSuccessMessage: true,
    })
}

export function redeemStake(data: anyObj) {
    if (mockApi) return mockApi.redeemStake(data)
    
    return createAxios({
        url: stakeUrl + 'redeem',
        method: 'post',
        data: data,
    }, {
        showSuccessMessage: true,
    })
}

export function claimProfit(data: anyObj) {
    if (mockApi) return mockApi.claimProfit(data)
    
    return createAxios({
        url: stakeUrl + 'claimProfit',
        method: 'post',
        data: data,
    }, {
        showSuccessMessage: true,
    })
}

// User Stake Information APIs
export function getUserStakes(params: anyObj) {
    if (mockApi) return mockApi.getUserStakes(params)
    
    return createAxios({
        url: stakeUrl + 'userStakes',
        method: 'get',
        params: params,
    })
}

export function getStakeDetails(id: number, userId: number) {
    if (mockApi) return mockApi.getStakeDetails(id, userId)
    
    return createAxios({
        url: stakeUrl + 'details/' + id,
        method: 'get',
        params: { user_id: userId }
    })
}

export function getUserSummary(userId: number, coinType?: string) {
    if (mockApi) return mockApi.getUserSummary(userId)
    
    const params: anyObj = { user_id: userId }
    if (coinType) params.coin_type = coinType
    
    return createAxios({
        url: stakeUrl + 'userSummary',
        method: 'get',
        params: params
    })
}

export function getProfitHistory(params: anyObj) {
    if (mockApi) return mockApi.getProfitHistory(params)
    
    return createAxios({
        url: stakeUrl + 'profitHistory',
        method: 'get',
        params: params
    })
}

// User Level System APIs
export function getStakeLevels() {
    if (mockApi) return mockApi.getStakeLevels()
    
    return createAxios({
        url: stakeUrl + 'levels',
        method: 'get',
    })
}

export function getUserLevel(userId: number) {
    if (mockApi) return mockApi.getUserLevel(userId)
    
    return createAxios({
        url: stakeUrl + 'userLevel',
        method: 'get',
        params: { user_id: userId }
    })
}

// Referral System APIs
export function getReferralInfo(userId: number) {
    if (mockApi) return mockApi.getReferralInfo(userId)
    
    return createAxios({
        url: stakeUrl + 'referralInfo',
        method: 'get',
        params: { user_id: userId }
    })
}

export function getReferralList(params: anyObj) {
    if (mockApi) return mockApi.getReferralList(params)
    
    return createAxios({
        url: stakeUrl + 'referralList',
        method: 'get',
        params: params
    })
}

export function getReferralRewards(params: anyObj) {
    if (mockApi) return mockApi.getReferralRewards(params)
    
    return createAxios({
        url: stakeUrl + 'referralRewards',
        method: 'get',
        params: params
    })
}

// Wallet Operations APIs
export function getWalletSummary(userId: number) {
    if (mockApi) return mockApi.getWalletSummary(userId)
    
    return createAxios({
        url: stakeUrl + 'walletSummary',
        method: 'get',
        params: { user_id: userId }
    })
}

export function getWalletTransactions(params: anyObj) {
    if (mockApi) return mockApi.getWalletTransactions(params)
    
    return createAxios({
        url: stakeUrl + 'walletTransactions',
        method: 'get',
        params: params
    })
}

export function getDepositInfo(userId: number) {
    if (mockApi) return mockApi.getDepositInfo(userId)
    
    return createAxios({
        url: stakeUrl + 'depositInfo',
        method: 'get',
        params: { user_id: userId }
    })
}

export function notifyDeposit(data: anyObj) {
    if (mockApi) return mockApi.notifyDeposit(data)
    
    return createAxios({
        url: stakeUrl + 'notifyDeposit',
        method: 'post',
        data: data
    }, {
        showSuccessMessage: true
    })
}

export function withdrawFunds(data: anyObj) {
    if (mockApi) return mockApi.withdrawFunds(data)
    
    return createAxios({
        url: stakeUrl + 'withdraw',
        method: 'post',
        data: data
    }, {
        showSuccessMessage: true
    })
}

export function cancelWithdrawal(data: anyObj) {
    if (mockApi) return mockApi.cancelWithdrawal(data)
    
    return createAxios({
        url: stakeUrl + 'cancelWithdrawal',
        method: 'post',
        data: data
    }, {
        showSuccessMessage: true
    })
}

// Activities and Promotions APIs
export function getActivities(status?: number) {
    // Skip mock check if the mock API doesn't support this method
    if (mockApi && 'getActivities' in mockApi) {
        return (mockApi as any).getActivities(status)
    }
    
    const params: anyObj = {}
    if (status !== undefined) params.status = status
    
    return createAxios({
        url: stakeUrl + 'activities',
        method: 'get',
        params: params
    })
}

// System Configuration APIs
export function getConfig() {
    if (mockApi) return mockApi.getConfig()
    
    return createAxios({
        url: stakeUrl + 'config',
        method: 'get',
    })
}

// Market Data APIs
export const marketUrl = '/api/market/'

export function getMarketData(params: anyObj = {}) {
    if (mockApi) return mockApi.getMarketData()
    
    return createAxios({
        url: marketUrl + 'data',
        method: 'get',
        params: params
    })
}

export function getMarketChart(symbol: string, timeframe: string = '1d') {
    // Skip mock check if the mock API doesn't support this method
    if (mockApi && 'getMarketChart' in mockApi) {
        return (mockApi as any).getMarketChart(symbol, timeframe)
    }
    
    return createAxios({
        url: marketUrl + 'chart',
        method: 'get',
        params: {
            symbol: symbol,
            timeframe: timeframe
        }
    })
}

export function getMarketTrending() {
    // Skip mock check if the mock API doesn't support this method
    if (mockApi && 'getMarketTrending' in mockApi) {
        return (mockApi as any).getMarketTrending()
    }
    
    return createAxios({
        url: marketUrl + 'trending',
        method: 'get'
    })
} 