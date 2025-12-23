// API 请求封装 - Mock版本
const BASE_URL = 'https://api.tradebot.example.com'; // 实际开发中替换为真实接口地址

// Mock数据 - 用户相关
const MOCK_USER_DATA = {
	login: {
		token: 'mock_token_12345',
		userInfo: {
			id: 1,
			username: 'testuser',
			email: 'test@example.com',
			phone: '13800138000',
			inviteCode: 'ABC123',
			level: 1,
			balance: 10000
		}
	},
	register: {
		success: true,
		message: '注册成功'
	},
	info: {
		id: 1,
		username: 'testuser',
		email: 'test@example.com',
		phone: '13800138000',
		inviteCode: 'ABC123',
		level: 1,
		balance: 10000,
		directInvites: 3,
		indirectInvites: 5,
		totalProfit: 1256.78
	}
};

// Mock数据 - 交易所相关
const MOCK_EXCHANGE_DATA = {
	connect: {
		success: true,
		message: '连接成功'
	},
	status: {
		connected: true,
		exchangeName: '币安 Binance',
		lastSync: '2023-03-11 14:23:45'
	},
	balance: {
		name: 'Trader001',
		totalBalance: '12,345.67',
		assets: [
			{ currency: 'BTC', available: '0.1256', valueInUSDT: '4,523.45' },
			{ currency: 'ETH', available: '2.3412', valueInUSDT: '3,678.90' },
			{ currency: 'USDT', available: '2,546.78', valueInUSDT: '2,546.78' },
			{ currency: 'BNB', available: '12.345', valueInUSDT: '1,234.56' }
		]
	}
};

// Mock数据 - 机器人相关
const MOCK_BOT_DATA = {
	start: {
		success: true,
		message: '机器人已启动'
	},
	stop: {
		success: true,
		message: '机器人已停止'
	},
	status: {
		running: true,
		startTime: '2023-03-11 14:25:30',
		currentStrategy: '平衡模式',
		todayProfit: '+2.34%',
		totalProfit: '+15.67%'
	},
	logs: {
		total: 10,
		logs: [
			{ time: '2023-03-11 15:12:06', type: 'info', content: '执行买入 BTC，数量 0.01，价格 $43,125.68' },
			{ time: '2023-03-11 15:00:01', type: 'info', content: '分析中，检测到BTC价格上涨趋势' },
			{ time: '2023-03-11 14:45:13', type: 'info', content: '执行卖出 ETH，数量 0.5，价格 $1,567.25' },
			{ time: '2023-03-11 14:25:30', type: 'success', content: '机器人启动成功' }
		]
	}
};

// Mock数据 - 交易相关
const MOCK_TRADE_DATA = {
	list: {
		total: 8,
		trades: [
			{ id: 1, pair: 'BTC/USDT', type: 'buy', price: '43,125.68', amount: '0.01', time: '2023-03-11 15:12:06', profit: '--' },
			{ id: 2, pair: 'ETH/USDT', type: 'sell', price: '1,567.25', amount: '0.5', time: '2023-03-11 14:45:13', profit: '+2.5%' },
			{ id: 3, pair: 'BNB/USDT', type: 'buy', price: '243.56', amount: '1.5', time: '2023-03-11 14:30:22', profit: '--' },
			{ id: 4, pair: 'SOL/USDT', type: 'sell', price: '84.32', amount: '5.0', time: '2023-03-11 14:15:40', profit: '-1.2%' },
			{ id: 5, pair: 'BTC/USDT', type: 'sell', price: '42,986.45', amount: '0.015', time: '2023-03-11 13:50:12', profit: '+1.8%' }
		]
	},
	detail: {
		id: 1,
		pair: 'BTC/USDT',
		type: 'buy',
		price: '43,125.68',
		amount: '0.01',
		total: '431.26',
		fee: '0.43',
		time: '2023-03-11 15:12:06',
		status: 'completed',
		orderId: '12345678',
		exchangeId: 'binance'
	},
	create: {
		success: true,
		orderId: '12345678',
		message: '订单创建成功'
	}
};

// 交易相关的mock数据
const mockTradeData = {
	trades: [
		{
			id: 'T123456789',
			pair: 'BTC/USDT',
			type: 'buy',
			price: '42,580.25',
			amount: '0.0235',
			total: '1000.64',
			fee: '1.00',
			status: 'completed',
			createTime: '2023-06-15 14:32:45',
			completeTime: '2023-06-15 14:33:12',
			marketPrice: '43,125.68',
			priceChange: 545.43,
			priceChangePercent: 1.28,
			profitAmount: 12.82
		},
		{
			id: 'T123456788',
			pair: 'ETH/USDT',
			type: 'sell',
			price: '1,578.35',
			amount: '0.6350',
			total: '1002.25',
			fee: '1.00',
			status: 'completed',
			createTime: '2023-06-14 09:15:22',
			completeTime: '2023-06-14 09:16:05',
			marketPrice: '1,567.25',
			priceChange: -11.10,
			priceChangePercent: -0.70,
			profitAmount: 7.04
		},
		{
			id: 'T123456787',
			pair: 'BNB/USDT',
			type: 'buy',
			price: '245.78',
			amount: '4.0750',
			total: '1001.55',
			fee: '1.00',
			status: 'completed',
			createTime: '2023-06-12 18:45:33',
			completeTime: '2023-06-12 18:46:10',
			marketPrice: '243.56',
			priceChange: -2.22,
			priceChangePercent: -0.90,
			profitAmount: -9.05
		},
		{
			id: 'T123456786',
			pair: 'SOL/USDT',
			type: 'buy',
			price: '82.45',
			amount: '12.1500',
			total: '1001.77',
			fee: '1.00',
			status: 'pending',
			createTime: '2023-06-10 11:22:45',
			completeTime: '',
			marketPrice: '84.32',
			priceChange: 0,
			priceChangePercent: 0,
			profitAmount: 0
		},
		{
			id: 'T123456785',
			pair: 'XRP/USDT',
			type: 'sell',
			price: '0.5725',
			amount: '1750.2200',
			total: '1002.00',
			fee: '1.00',
			status: 'failed',
			createTime: '2023-06-08 15:33:21',
			completeTime: '2023-06-08 15:34:05',
			marketPrice: '0.5678',
			priceChange: 0,
			priceChangePercent: 0,
			profitAmount: 0
		}
	]
};

// 请求拦截器
const requestInterceptor = (config) => {
	// 获取token
	const token = uni.getStorageSync('userToken');
	if (token) {
		config.header = {
			...config.header,
			'Authorization': `Bearer ${token}`
		};
	}
	return config;
};

// Mock请求方法 - 替代实际网络请求
const mockRequest = (options) => {
	return new Promise((resolve, reject) => {
		console.log('Mock API 请求:', options.url, options.method, options.data);
		
		// 模拟网络延迟
		setTimeout(() => {
			try {
				// 基于URL和方法返回不同的mock数据
				let result = { code: 0, data: {}, message: 'success' };
				
				// 用户相关接口
				if (options.url.includes('/user/login')) {
					result.data = MOCK_USER_DATA.login;
				} 
				else if (options.url.includes('/user/register')) {
					result.data = MOCK_USER_DATA.register;
				}
				else if (options.url.includes('/user/info')) {
					result.data = MOCK_USER_DATA.info;
				}
				// 交易所相关接口
				else if (options.url.includes('/exchange/connect')) {
					result.data = MOCK_EXCHANGE_DATA.connect;
				}
				else if (options.url.includes('/exchange/status')) {
					result.data = MOCK_EXCHANGE_DATA.status;
				}
				else if (options.url.includes('/exchange/balance')) {
					result.data = MOCK_EXCHANGE_DATA.balance;
				}
				// 机器人相关接口
				else if (options.url.includes('/bot/start')) {
					result.data = MOCK_BOT_DATA.start;
				}
				else if (options.url.includes('/bot/stop')) {
					result.data = MOCK_BOT_DATA.stop;
				}
				else if (options.url.includes('/bot/status')) {
					result.data = MOCK_BOT_DATA.status;
				}
				else if (options.url.includes('/bot/logs')) {
					result.data = MOCK_BOT_DATA.logs;
				}
				// 交易相关接口
				else if (options.url.includes('/trade/list')) {
					result.data = MOCK_TRADE_DATA.list;
				}
				else if (options.url.includes('/trade/detail')) {
					result.data = MOCK_TRADE_DATA.detail;
				}
				else if (options.url.includes('/trade/create')) {
					result.data = MOCK_TRADE_DATA.create;
				}
				else {
					// 未知接口，返回空数据
					console.warn('未知Mock接口:', options.url);
				}
				
				resolve(result.data);
			} catch (error) {
				console.error('Mock API 错误:', error);
				reject('请求处理失败');
			}
		}, 500); // 模拟500ms网络延迟
	});
};

// 各种请求方法
const api = {
	get: (url, params) => {
		return mockRequest({
			url,
			method: 'GET',
			data: params
		});
	},
	post: (url, data) => {
		return mockRequest({
			url,
			method: 'POST',
			data
		});
	},
	put: (url, data) => {
		return mockRequest({
			url,
			method: 'PUT',
			data
		});
	},
	delete: (url, data) => {
		return mockRequest({
			url,
			method: 'DELETE',
			data
		});
	}
};

// API 接口定义
const API = {
	// 用户相关
	user: {
		login: (data) => api.post('/user/login', data),
		register: (data) => api.post('/user/register', data),
		getInfo: () => api.get('/user/info')
	},
	// 交易所相关
	exchange: {
		connect: (data) => api.post('/exchange/connect', data),
		status: () => api.get('/exchange/status'),
		getBalance: () => api.get('/exchange/balance')
	},
	// 机器人相关
	bot: {
		start: () => api.post('/bot/start'),
		stop: () => api.post('/bot/stop'),
		status: () => api.get('/bot/status'),
		logs: (params) => api.get('/bot/logs', params)
	},
	// 交易相关 - 新增
	trade: {
		list: (params) => {
			return mockRequest({
				trades: mockTradeData.trades.slice(0, params.limit || mockTradeData.trades.length)
			});
		},
		detail: (params) => {
			const trade = mockTradeData.trades.find(t => t.id === params.id);
			return mockRequest({
				trade: trade || {
					id: params.id,
					pair: 'BTC/USDT',
					type: 'buy',
					price: '43,125.68',
					amount: '0.0232',
					total: '1000.52',
					fee: '1.00',
					status: 'completed',
					createTime: '2023-06-16 10:25:33',
					completeTime: '2023-06-16 10:26:12',
					marketPrice: '43,250.45',
					priceChange: 124.77,
					priceChangePercent: 0.29,
					profitAmount: 2.89
				}
			});
		},
		create: (params) => {
			const id = 'T' + Date.now().toString().substring(3);
			const now = new Date();
			const createTime = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`;
			
			const price = parseFloat(params.price.replace(/,/g, ''));
			const amount = parseFloat(params.amount);
			const total = (price * amount).toFixed(2);
			const fee = (total * 0.001).toFixed(2);
			
			const newTrade = {
				id,
				pair: params.pair,
				type: params.type,
				price: params.price,
				amount: params.amount,
				total,
				fee,
				status: 'pending',
				createTime,
				completeTime: '',
				marketPrice: 0,
				priceChange: 0,
				priceChangePercent: 0,
				profitAmount: 0
			};
			
			// 添加到交易列表
			mockTradeData.trades.unshift(newTrade);
			
			// 模拟交易完成
			setTimeout(() => {
				const index = mockTradeData.trades.findIndex(t => t.id === id);
				if (index !== -1) {
					const completeTime = new Date();
					mockTradeData.trades[index].status = 'completed';
					mockTradeData.trades[index].completeTime = `${completeTime.getFullYear()}-${String(completeTime.getMonth() + 1).padStart(2, '0')}-${String(completeTime.getDate()).padStart(2, '0')} ${String(completeTime.getHours()).padStart(2, '0')}:${String(completeTime.getMinutes()).padStart(2, '0')}:${String(completeTime.getSeconds()).padStart(2, '0')}`;
					
					// 模拟市场价格和盈亏
					const marketPrice = price * (1 + (Math.random() * 0.04 - 0.02));
					const priceChange = (marketPrice - price).toFixed(2);
					const priceChangePercent = ((marketPrice - price) / price * 100).toFixed(2);
					const profitAmount = (priceChange * amount).toFixed(2);
					
					mockTradeData.trades[index].marketPrice = marketPrice.toFixed(2);
					mockTradeData.trades[index].priceChange = priceChange;
					mockTradeData.trades[index].priceChangePercent = priceChangePercent;
					mockTradeData.trades[index].profitAmount = profitAmount;
				}
			}, 3000);
			
			return mockRequest({ id });
		},
		cancel: (params) => {
			const index = mockTradeData.trades.findIndex(t => t.id === params.id);
			if (index !== -1) {
				mockTradeData.trades[index].status = 'canceled';
			}
			return mockRequest({ success: true });
		}
	}
};

export default API; 