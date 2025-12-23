/**
 * Common utility functions
 */

/**
 * Check if user is logged in
 * @returns {boolean} True if user is logged in
 */
export const checkLogin = () => {
	const token = uni.getStorageSync('token');
	return !!token;
};

/**
 * Format price to USD currency string
 * @param {number} price - The price to format
 * @returns {string} Formatted price string
 */
export const formatPrice = (price) => {
	if (price === undefined || price === null) return '$0.00';
	
	if (price < 0.01) {
		return '$' + price.toFixed(6);
	} else if (price < 1) {
		return '$' + price.toFixed(4);
	} else if (price < 10) {
		return '$' + price.toFixed(2);
	} else if (price < 1000) {
		return '$' + price.toFixed(2);
	} else if (price < 10000) {
		return '$' + price.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	} else {
		return '$' + price.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
	}
};

/**
 * Format large numbers (for market cap and volume)
 * @param {number} num - The number to format
 * @returns {string} Formatted number string
 */
export const formatLargeNumber = (num) => {
	if (num === undefined || num === null) return '$0';
	
	if (num >= 1000000000000) {
		return '$' + (num / 1000000000000).toFixed(2) + 'T';
	} else if (num >= 1000000000) {
		return '$' + (num / 1000000000).toFixed(2) + 'B';
	} else if (num >= 1000000) {
		return '$' + (num / 1000000).toFixed(2) + 'M';
	} else if (num >= 1000) {
		return '$' + (num / 1000).toFixed(2) + 'K';
	} else {
		return '$' + num.toFixed(2);
	}
};

/**
 * Format percentage change
 * @param {number} change - The percentage change
 * @returns {string} Formatted percentage string
 */
export const formatChange = (change) => {
	if (change === undefined || change === null) return '0.00%';
	
	let symbol = change >= 0 ? '+' : '';
	return symbol + change.toFixed(2) + '%';
};

/**
 * Format date to locale string
 * @param {number|string|Date} timestamp - Timestamp or date string
 * @returns {string} Formatted date string
 */
export const formatDate = (timestamp) => {
	const date = new Date(timestamp);
	return date.toLocaleDateString();
};

/**
 * Format time to locale string
 * @param {number|string|Date} timestamp - Timestamp or date string
 * @returns {string} Formatted time string
 */
export const formatTime = (timestamp) => {
	const date = new Date(timestamp);
	return date.toLocaleTimeString();
};

/**
 * Format datetime to locale string
 * @param {number|string|Date} timestamp - Timestamp or date string
 * @returns {string} Formatted datetime string
 */
export const formatDateTime = (timestamp) => {
	const date = new Date(timestamp);
	return date.toLocaleString();
};

/**
 * Get common coin image URL by symbol
 * @param {string} symbol - Coin symbol
 * @returns {string} Coin image URL
 */
export const getCoinImageUrl = (symbol) => {
	// Default coins mapping
	const coinImages = {
		'btc': 'https://assets.coingecko.com/coins/images/1/small/bitcoin.png',
		'eth': 'https://assets.coingecko.com/coins/images/279/small/ethereum.png',
		'usdt': 'https://assets.coingecko.com/coins/images/325/small/Tether.png',
		'bnb': 'https://assets.coingecko.com/coins/images/825/small/bnb-icon2_2x.png',
		'sol': 'https://assets.coingecko.com/coins/images/4128/small/solana.png',
		'xrp': 'https://assets.coingecko.com/coins/images/44/small/xrp-symbol-white-128.png',
		'ada': 'https://assets.coingecko.com/coins/images/975/small/cardano.png',
		'doge': 'https://assets.coingecko.com/coins/images/5/small/dogecoin.png',
		'dot': 'https://assets.coingecko.com/coins/images/12171/small/polkadot.png',
		'link': 'https://assets.coingecko.com/coins/images/877/small/chainlink.png'
	};
	
	const lowerSymbol = symbol.toLowerCase();
	return coinImages[lowerSymbol] || 'https://assets.coingecko.com/coins/images/1/small/bitcoin.png';
};

/**
 * Truncate text with ellipsis
 * @param {string} text - Text to truncate
 * @param {number} length - Maximum length
 * @returns {string} Truncated text
 */
export const truncateText = (text, length = 20) => {
	if (text && text.length > length) {
		return text.substring(0, length) + '...';
	}
	return text;
};

/**
 * Debounce function to limit function execution rate
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} Debounced function
 */
export const debounce = (func, wait = 300) => {
	let timeout;
	return function executedFunction(...args) {
		const later = () => {
			clearTimeout(timeout);
			func(...args);
		};
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
	};
};

/**
 * Copy text to clipboard
 * @param {string} text - Text to copy
 * @returns {Promise} Promise that resolves when copied
 */
export const copyToClipboard = (text) => {
	return new Promise((resolve, reject) => {
		uni.setClipboardData({
			data: text,
			success: function() {
				uni.showToast({
					title: '复制成功',
					icon: 'success'
				});
				resolve(true);
			},
			fail: function(error) {
				uni.showToast({
					title: '复制失败',
					icon: 'none'
				});
				reject(error);
			}
		});
	});
};

/**
 * 退出登录
 */
export const logout = () => {
	uni.removeStorageSync('userToken');
	uni.removeStorageSync('userInfo');
	
	uni.reLaunch({
		url: '/pages/login/index'
	});
};

/**
 * 检查交易所是否已连接
 * @returns {Boolean} 连接状态
 */
export const checkExchangeConnected = () => {
	return !!uni.getStorageSync('connectedExchange');
};

/**
 * 格式化货币金额
 * @param {Number|String} amount 金额
 * @param {Number} decimals 小数位数
 * @returns {String} 格式化后的金额
 */
export const formatCurrency = (amount, decimals = 2) => {
	if (!amount && amount !== 0) return '0.00';
	
	const num = typeof amount === 'string' ? parseFloat(amount) : amount;
	
	if (isNaN(num)) return '0.00';
	
	return num.toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};

/**
 * 验证邮箱格式
 * @param {String} email 邮箱
 * @returns {Boolean} 验证结果
 */
export const validateEmail = (email) => {
	const reg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/;
	return reg.test(email);
};

/**
 * 验证手机号格式
 * @param {String} phone 手机号
 * @returns {Boolean} 验证结果
 */
export const validatePhone = (phone) => {
	const reg = /^1[3-9]\d{9}$/;
	return reg.test(phone);
};

/**
 * 生成随机邀请码
 * @param {Number} length 长度
 * @returns {String} 邀请码
 */
export const generateInviteCode = (length = 6) => {
	const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	let result = '';
	for (let i = 0; i < length; i++) {
		result += chars.charAt(Math.floor(Math.random() * chars.length));
	}
	return result;
}; 