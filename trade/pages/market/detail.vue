<template>
	<view class="coin-detail-container"> 
        <!-- 币种标题和收藏按钮 
			<view class="coin-title-bar">
				<image
					class="back-icon"
					src="/static/img/market/detail/backicon.png"
					@tap="goBack"
				></image>
				<text class="coin-title-text">{{ coin.symbol ? coin.symbol.toUpperCase() : 'BTC' }}</text>
				<image
					class="log-icon"
					src="/static/img/market/detail/logicon.png"
					@tap="goToHistory"
				></image>
			</view> -->
		<!-- 币种头部信息 -->
		<view class="coin-header">
			

			<!-- 账户余额和交易模式切换 -->
			<view class="balance-cards">
				<view class="balance-card">
					<view class="card-icon-wrapper">
						<image class="card-icon-img" :src="'/static/img/market/detail/money.png'"></image>
					</view>
					<view class="card-info">
						<text class="card-label">{{ isRealTrade ? $t('账户余额') : $t('模拟余额') }}</text>
						<text class="card-value">${{ isRealTrade ? accountBalance : demoBalance }}</text>
					</view>
				</view>
				<view :class="['balance-card1', 'trade-mode-switch', isRealTrade ? 'real-trade-mode' : '']" @tap="toggleTradeMode">
					<view class="switch-content">
						<image class="switch-icon" :src="'/static/img/market/detail/change.png'"></image>
						<text class="switch-text">{{ isRealTrade ? $t('切换模拟交易') : $t('切换真实交易') }}</text>
					</view>
				</view>
				<view class="history-column" @tap="goToHistory">
					<image
						class="log-icon"
						src="/static/img/market/detail/logicon.png"
					></image>
					<text class="history-text">{{ $t('历史') }}</text>
				</view>
			</view>

			<!-- 币种价格信息 
			<view class="price-display">
				<view class="price-left-section"> 
					<view >
					<view class="price-top-row">
						<image class="coin-icon" src="/static/img/market/detail/btcicon.png"></image>
						<text class="price-pair">{{ coin.symbol ? coin.symbol.toUpperCase() : 'BTC' }}/USDT</text>
						<view class="change-badge">
							<image
								class="change-arrow"
								:src="coin.price_change_percentage_24h >= 0 ? '/static/img/market/detail/arrow-up.png' : '/static/img/market/detail/arrow-down.png'"
							></image>
							<text :class="['change-text', coin.price_change_percentage_24h >= 0 ? 'positive' : 'negative']">
								{{ coin.price_change_percentage_24h >= 0 ? '+' : '' }}{{ coin.price_change_percentage_24h ? coin.price_change_percentage_24h.toFixed(2) : '0.00' }}%
							</text>
						</view>
						
					</view> 
					<text class="price-value">${{ formatPrice(coin.current_price) }}</text>
					</view> 
				</view>  
				<image class="kline-icon" src="/static/img/market/detail/line-chat.png"></image>
			</view> -->
		</view>

		<!-- 时间周期选择器 -->
		<view class="time-period-selector">
			<!-- 交易对选择器 -->
			<view class="pair-selector-wrapper">
				<view class="pair-selector" @tap.stop="togglePairDropdown">
					<text class="pair-text">{{ currentPair.code || 'BTCUSDT' }}&nbsp;&nbsp;{{ showPairPopup ? '▲' : '▼' }}</text>
				</view>
				<!-- 交易对下拉框 -->
				<view v-if="showPairPopup" class="pair-dropdown" @tap.stop>
					<scroll-view class="pair-dropdown-list" scroll-y>
						<view
							v-for="(pair, index) in tradePairList"
							:key="index"
							:class="['pair-dropdown-item', currentPair.id === pair.id ? 'pair-item-active' : '']"
							@tap="selectTradePair(pair)"
						>
							<image class="pair-item-icon" :src="pair.icon?H5BaseUrl+pair.icon:getDefaultIcon(pair.code)"></image>
							<view class="pair-item-info">
								<text class="pair-item-name">{{ pair.code }}</text>
							</view>
							<text v-if="currentPair.id === pair.id" class="pair-item-check-icon">✓</text>
						</view>
					</scroll-view>
				</view>
			</view>
			<view
				v-for="(period, index) in timePeriods"
				:key="index"
				:class="['period-item', selectedPeriod === period ? 'period-active' : '']"
				@tap="selectPeriod(period)"
			>
				<text class="period-text">{{ period }}</text>
			</view>
			<!-- 更多时间周期下拉框 -->
			<view class="period-more-wrapper">
				<view
					:class="['period-item', 'period-more', isMorePeriodSelected ? 'period-active' : '']"
					@tap.stop="toggleMorePeriods"
				>
					<text class="period-text">{{ morePeriodLabel }}</text>
				</view>
				<!-- 更多周期下拉框 -->
				<view v-if="showMorePeriods" class="period-dropdown" @tap.stop>
					<view
						v-for="(item, index) in moreTimePeriods"
						:key="index"
						:class="['period-dropdown-item', selectedPeriod === item.value ? 'period-item-active' : '']"
						@tap="selectMorePeriod(item)"
					>
						<text class="period-dropdown-text">{{ item.label }}</text>
					</view>
				</view>
			</view>
		</view>

		<!-- 点击其他区域关闭下拉框的遮罩 -->
		<view v-if="showPairPopup || showMorePeriods" class="pair-dropdown-overlay" @tap="closeAllDropdowns"></view>
		
		<!-- 图表区域 (保留原有的LightweightChart) -->
		<view class="chart-container">
			<!-- 当前价格标签显示在图表左上角 -->
			<view class="chart-price-label">
				<text class="chart-price-text">${{ coin.current_price }}</text>
			</view>

			<!-- Lightweight Chart 图表 - 保持不变 -->
			<view class="trading-view-wrapper">
				<LightweightChart
					ref="chart"
					:symbol="tradingSymbol"
					:interval="chartInterval"
					theme="dark"
					height="100%"
					:autoConnect="true"
					@ready="onChartReady"
					@error="onChartError"
					@price-update="onPriceUpdate"
					@data-loaded="onDataLoaded"
					@chart-rebuilt="onChartRebuilt"
				/>
			</view>
		</view>
 

		<!-- 当前委托订单 -->
		<view class="order-section">
			<scroll-view scroll-x="true" class="countdown-timers">
				<view class="countdown-timers-inner">
					<view
						v-for="(order, index) in pendingOrders"
						:key="index"
						class="timer-item"
					>
						<!-- 金蛋倒计时：静态 PNG + 抖动效果，结束后根据结果切换GIF -->
						<view class="egg-timer" v-if="!order.eggResult || order.eggResult !== 'draw'">
							<view class="egg-wrapper" :class="{ 'egg-wrapper-result': order.eggResult }">
								<!-- 静态金蛋：倒计时时轻微抖动，结束后根据结果显示不同图片 -->
								<image
									class="egg-static"
									:class="[
										order.remainingSeconds > 0 && !order.playingEgg ? 'egg-static-shake' : '',
										order.eggResult=='win' ? 'egg-result-gif1' : '',
										order.eggResult=='lose' ? 'egg-result-gif' : ''
									]"
									:src="getEggImageSrc(order)"
									:mode="order.eggResult ? 'aspectFill' : 'aspectFit'"
								/>
								<!-- 剩余时间倒计时（仅在倒计时过程中显示） -->
								<view class="egg-countdown" v-if="order.remainingSeconds > 0">
									<text class="egg-timer-value">{{ formatCountdown(order.remainingSeconds) }}</text>
								</view>
								<!-- 币种名称移动到倒计时下面，显示在金蛋内部底部区域 -->
								<view class="egg-coin-label">
									<text class="egg-coin-text">{{ order.coin_symbol }}</text>
								</view>
							</view>
						</view>
					</view>
				</view>
			</scroll-view>
		</view>

		<!-- Time 和 Amount 标签 -->
		<view class="trade-labels-header">
			<text class="label-header-text">{{ $t('Time') }}</text>
			<text class="label-header-text">{{ $t('Amount') }}</text>
		</view>

		<!-- 交易选项网格 -->
		<view class="trade-options-grid">
			<!-- Time 选择 -->
			<view class="time-selection-wrapper">
				<scroll-view class="time-grid-scroll" scroll-x :show-scrollbar="false">
					<view class="time-grid">
						<view
							v-for="(time, index) in tradeTimeOptions"
							:key="'time-' + index"
							:class="['time-grid-item', selectedTradeTime === time.value ? 'grid-item-active' : '']"
							@tap="selectTradeTime(time.value)"
						>
							<text class="grid-item-label">{{ time.label }}</text>
							<text class="grid-item-rate">{{ time.rate }}</text>
						</view>
					</view>
				</scroll-view>
				<!-- 选中的 Time 显示 -->
				<view class="selection-display">
					<text class="display-label">{{ $t('Time') }}</text>
					<text class="display-value">{{ selectedTradeTime }}</text>
				</view>
			</view>

			<!-- Amount 选择 -->
			<view class="amount-selection-wrapper">
				<view class="amount-grid">
					<view
						v-for="(amount, index) in amountOptions"
						:key="'amount-' + index"
						:class="['amount-grid-item', selectedAmount === amount ? 'grid-item-active' : '']"
						@tap="selectAmount(amount)"
					>
						<text class="grid-item-label">${{ amount }}</text>
						<text class="grid-item-rate">&nbsp;</text>
					</view>
				</view>
				<!-- 选中的 Amount 显示 -->
				<view class="selection-display" @tap="openKeyboard('amount')">
					<text class="display-label">{{ $t('Amount') }}</text>
					<text class="display-value">${{ selectedAmount }}</text>
				</view>
			</view>
		</view>

		<!-- 底部小键盘 -->
		<view v-if="showKeyboard" class="keyboard-overlay" @tap="closeKeyboard">
			<view class="keyboard-container" @tap.stop>
				<view class="keyboard-header">
					<text class="keyboard-title">{{ keyboardType === 'time' ? $t('输入时间(秒)') : $t('输入金额') }}</text>
					<text class="keyboard-input-display">{{ keyboardInput || '0' }}</text>
				</view>
				<view class="keyboard-grid">
					<!-- 第一行: 0-4 + 删除 -->
					<view class="keyboard-row">
						<view class="keyboard-key" @tap="onKeyPress('0')"><text>0</text></view>
						<view class="keyboard-key" @tap="onKeyPress('1')"><text>1</text></view>
						<view class="keyboard-key" @tap="onKeyPress('2')"><text>2</text></view>
						<view class="keyboard-key" @tap="onKeyPress('3')"><text>3</text></view>
						<view class="keyboard-key" @tap="onKeyPress('4')"><text>4</text></view>
						<view class="keyboard-key key-delete" @tap="onKeyDelete"><text>{{ $t('删除') }}</text></view>
					</view>
					<!-- 第二行: 5-9 + 确认 -->
					<view class="keyboard-row">
						<view class="keyboard-key" @tap="onKeyPress('5')"><text>5</text></view>
						<view class="keyboard-key" @tap="onKeyPress('6')"><text>6</text></view>
						<view class="keyboard-key" @tap="onKeyPress('7')"><text>7</text></view>
						<view class="keyboard-key" @tap="onKeyPress('8')"><text>8</text></view>
						<view class="keyboard-key" @tap="onKeyPress('9')"><text>9</text></view>
						<view class="keyboard-key key-confirm" @tap="onKeyConfirm"><text>{{ $t('确认') }}</text></view>
					</view>
				</view>
			</view>
		</view>
		
		<!-- 买涨买跌按钮 -->
		<view class="trade-buttons">
			<view class="trade-btn buy-up" @tap="confirmTrade('up')">
				<text class="btn-text">{{ $t('涨') }}</text>
			</view>
			<view class="trade-btn buy-down" @tap="confirmTrade('down')">
				<text class="btn-text">{{ $t('跌') }}</text>
			</view>
		</view>

	</view>
</template>

<script>
// 引入API工具
import api from '@/utils/api.js';
import LightweightChart from '@/components/LightweightChart/LightweightChart.vue';

export default {
	components: {
		LightweightChart
	},
	data() {
		return {
			coinId: '',
			coin: {},
			chartLoading: true,
			// TradingView 交易对符号
			tradingSymbol: 'BTCUSDT',

			// 交易对相关
			tradePairList: [], // 交易对列表
			currentPair: {}, // 当前选中的交易对
			showPairPopup: false, // 是否显示交易对弹框

			// 新增：顶部状态栏
			currentTime: '9:41',
			accountBalance: '0', // 账户余额
			demoBalance: '0', // 模拟交易余额
			isRealTrade: true, // 是否真实交易模式，默认true

			// 新增：时间周期选择
			timePeriods: ['1M', '3M', '5M','15M'],
			selectedPeriod: '1M', // 默认选中1分钟
			chartInterval: '1m', // 图表时间间隔
			showMorePeriods: false, // 是否显示更多周期下拉框
			moreTimePeriods: [  
				{ label: '30M', value: '30m' },
				{ label: '1H', value: '1h' },
				{ label: '4H', value: '4h' }
			],

			// 新增：时间刻度
			timeScales: ['01.00', '02.00', '03.00', '04.00', '05.00', '06.00', '07.00'],

		// 新增：待处理订单（圆形进度条）
			pendingOrders: [
			],
			orderTimer: null, // 订单倒计时定时器

			// 交易相关
			tradeAmount: '', // 用户输入的交易金额
			holdingAmount: 0, // 持有的币种数量
			holdingUsdtAmount: 0, // 持有的USDT价值
			isProcessing: false, // 是否正在处理交易
			userId: 0, // 用户ID

			// 交易界面数据
			orderCount: 3, // 当前委托订单数量
			selectedTimeIndex: 0, // 选中的时间索引
			timeOptions: [
				{ value: '00:59' },
				{ value: '00:14' },
				{ value: '00:01' }
			],
			selectedTradeTime: '', // 选中的交易时间，默认无选中
			tradeTimeOptions: [
				{ label: 'M1', value: 'M1', rate: '92%', seconds: 60 },
				{ label: 'M2', value: 'M2', rate: '92%', seconds: 120 },
				{ label: 'M3', value: 'M3', rate: '92%', seconds: 180 },
				{ label: 'M5', value: 'M5', rate: '92%', seconds: 300 },
				{ label: 'M10', value: 'M10', rate: '92%', seconds: 600 },
				{ label: 'M15', value: 'M15', rate: '92%', seconds: 900 }
			],
			selectedAmount: null, // 选中的金额，默认无选中
			amountOptions: [10, 30, 50, 100], // 金额选项

			// 小键盘相关
			showKeyboard: false, // 是否显示小键盘
			keyboardType: '', // 键盘类型: 'time' 或 'amount'
			keyboardInput: '' // 键盘输入值
		}
	},
	computed: {
		// 判断当前选中的周期是否在更多选项中
		isMorePeriodSelected() {
			return this.moreTimePeriods.some(item => item.value === this.selectedPeriod);
		},
		// 更多周期按钮显示的文字
		morePeriodLabel() {
			const found = this.moreTimePeriods.find(item => item.value === this.selectedPeriod);
			const arrow = this.showMorePeriods ? '▲' : '▼';
			return found ? (found.label + arrow) : arrow;
		}
	},
	onLoad(options) {
		// 根据登录状态和用户偏好初始化交易模式（真实/模拟）
		this.updateTradeModeByLoginStatus();

		// 设置状态栏为暗色主题
		uni.setNavigationBarColor({
			frontColor: '#ffffff',
			backgroundColor: '#1A1A1A'
		});
        // 首先加载交易对列表
		this.loadTradePairList(options);

	},
	onShow() {
		// 每次进入页面时，根据当前登录状态自动切换交易模式
		this.updateTradeModeByLoginStatus();

		// 检查是否有从首页传递过来的交易对code
		const selectedCode = uni.getStorageSync('selectedTradeCode');
		if (selectedCode && this.tradePairList.length > 0) {
			// 查找对应的交易对
			const targetPair = this.tradePairList.find(p => p.code === selectedCode);
			if (targetPair && this.currentPair.code !== selectedCode) {
				// 切换到目标交易对
				this.selectTradePair(targetPair, false);
			}
			// 清除存储的code
			uni.removeStorageSync('selectedTradeCode');
		}

		// 加载余额和委托订单（仅当交易对已加载时）
		if (this.currentPair && this.currentPair.id) {
			this.loadBalance();
			this.loadPendingOrders();
		}

		// 隐藏客服按钮
		this.toggleCustomerService(false);
	},
	onUnload() {
		// 停止订单倒计时
		this.stopOrderTimer();
		// 显示客服按钮
		this.toggleCustomerService(true);
	},
	onHide() {
		// 页面隐藏时显示客服按钮
		this.toggleCustomerService(true);
	},
	methods: {
		// 根据登录状态和本地偏好更新交易模式
		updateTradeModeByLoginStatus() {
			// 重新获取用户ID（可能刚登录或退出登录）
			this.userId = uni.getStorageSync('userid');
			const storedMode = uni.getStorageSync('tradeMode'); // 'real' | 'demo' | undefined

			if (this.userId) {
				// 已登录：如果用户手动切换过模式就尊重用户选择，否则默认真实交易
				if (storedMode === 'demo') {
					this.isRealTrade = false;
				} else {
					this.isRealTrade = true;
				}
			} else {
				// 未登录：始终使用模拟交易（游客）
				this.isRealTrade = false;

				// 确保游客用户已初始化
				const deviceId = uni.getStorageSync('device_id');
				if (!deviceId) {
					this.initGuestUser();
				}
			}
		},

		// 控制客服按钮显示/隐藏
		toggleCustomerService(show) {
			// #ifdef H5
			try {
				// SaleSmartly 客服插件的常见选择器
				const selectors = [
					'#ss-chat',
					'#ss-chat-widget',
					'[id^="ss-"]',
					'.ss-chat',
					'.salesmartly',
					'iframe[src*="salesmartly"]',
					'div[class*="salesmartly"]',
					'div[id*="salesmartly"]'
				];

				selectors.forEach(selector => {
					const elements = document.querySelectorAll(selector);
					elements.forEach(el => {
						if (el) {
							el.style.display = show ? '' : 'none';
							el.style.visibility = show ? 'visible' : 'hidden';
						}
					});
				});

				// 尝试通过 SaleSmartly API 控制
				if (window.__ssc) {
					if (show) {
						window.__ssc('show');
					} else {
						window.__ssc('hide');
					}
				}
			} catch (e) {
				console.log('客服按钮控制失败:', e);
			}
			// #endif
		},

		// 将币种符号转换为 CoinGecko ID
		getCoinIdFromSymbol(symbol) {
			// 常见币种符号到 CoinGecko ID 的映射
			const symbolMap = {
				'btc': 'bitcoin',
				'eth': 'ethereum',
				'usdt': 'tether',
				'bnb': 'binancecoin',
				'sol': 'solana',
				'xrp': 'ripple',
				'usdc': 'usd-coin',
				'ada': 'cardano',
				'avax': 'avalanche-2',
				'doge': 'dogecoin',
				'trx': 'tron',
				'dot': 'polkadot',
				'matic': 'matic-network',
				'link': 'chainlink',
				'shib': 'shiba-inu',
				'dai': 'dai',
				'ltc': 'litecoin',
				'bch': 'bitcoin-cash',
				'uni': 'uniswap',
				'atom': 'cosmos',
				'etc': 'ethereum-classic',
				'xlm': 'stellar',
				'vet': 'vechain',
				'fil': 'filecoin',
				'icp': 'internet-computer',
				'apt': 'aptos',
				'arb': 'arbitrum',
				'op': 'optimism',
				'sui': 'sui',
				'near': 'near',
				'algo': 'algorand',
				'hbar': 'hedera-hashgraph',
				'qnt': 'quant-network',
				'grt': 'the-graph',
				'aave': 'aave',
				'stx': 'blockstack'
			};
			
			return symbolMap[symbol] || symbol;
		},
		
		// 加载币种数据
		loadCoinData() {
			this.chartLoading = true;
			
			// 初始化默认值，防止undefined错误
			this.coin = {
				name: '',
				symbol: '',
				image: null,
				current_price: 0,
				price_change_percentage_24h: 0,
				market_cap: 0,
				total_volume: 0,
				high_24h: 0,
				low_24h: 0,
				circulating_supply: 0,
				total_supply: 0,
				market_cap_rank: null
			};
			
			// 使用较长的缓存时间，减少API请求
			api.getCoin(this.coinId, 10) // 缓存10分钟
				.then(data => {
					if (data) {
						//console.log("API返回的币种数据:", data);
						
						// 正确提取图像 URL
						let imageUrl = null;
						if (data.image) {
							// API 可能返回对象形式的图像
							if (typeof data.image === 'object') {
								imageUrl = data.image.large || data.image.small || data.image.thumb;
							} else if (typeof data.image === 'string') {
								imageUrl = data.image;
							}
						}
						
						// 如果没有图像，使用 CoinGecko 默认图像
						if (!imageUrl && data.symbol) {
							imageUrl = `https://assets.coingecko.com/coins/images/1/large/${data.symbol.toLowerCase()}.png`;
						}
						
						this.coin = {
							...this.coin, // 保留默认值
							...data,
							image: imageUrl,
							current_price: data.market_data?.current_price?.usd || 0,
							price_change_percentage_24h: data.market_data?.price_change_percentage_24h || 0,
							market_cap: data.market_data?.market_cap?.usd || 0,
							total_volume: data.market_data?.total_volume?.usd || 0,
							high_24h: data.market_data?.high_24h?.usd || 0,
							low_24h: data.market_data?.low_24h?.usd || 0,
							circulating_supply: data.market_data?.circulating_supply || 0,
							total_supply: data.market_data?.total_supply || 0
						};
					}
					
					// 更新TradingView交易对符号
					this.updateTradingSymbol();
					this.chartLoading = false;
				})
				.catch(err => {
					console.error('Failed to load coin data:', err);
					uni.showToast({
						title: this.$t('加载币种数据失败'),
						icon: 'none'
					});
					this.chartLoading = false;
				});
		},
		
		// 检查图像 URL 是否有效
		isValidImageUrl(url) {
			if (!url) return false;
			return (
				url.startsWith('http://') || 
				url.startsWith('https://') || 
				url.startsWith('/static/')
			);
		},
		
		// 加载持仓数量
		loadHoldingAmount() {
			if (!this.userId) return;
			
			this.req({
				url: "bot/holding",
				data: {
					coin_id: this.coinId,
					userid: this.userId
				},
				success: (res) => {
					if (res.code == 1 && res.data.holdings) {
						// 在数组中查找匹配当前币种symbol的持仓数据
						const currentHolding = res.data.holdings.find(item => 
							item.symbol.toUpperCase() === (this.coin.symbol ? this.coin.symbol.toUpperCase() : '')
						);
						
						// 如果找到匹配的持仓数据，更新持仓数量
						if (currentHolding) {
							this.holdingAmount = parseFloat(currentHolding.amount) || 0;
						} else {
							this.holdingAmount = 0;
						}
						
						// 计算USDT价值 
						this.holdingUsdtAmount = this.holdingAmount * this.coin.current_price;
					}
				}
			});
		},

		// 加载用户余额
		loadBalance() {
			const t = this;

			// 如果是模拟交易且没有登录，使用游客接口
			if (!t.isRealTrade && !t.userId) {
				const deviceId = uni.getStorageSync('device_id');
				if (deviceId) {
					uni.request({
						url: t.siteBaseUrl + 'user/guestBalance',
						method: 'POST',
						data: { device_id: deviceId },
						success: function(res) {
							if (res.data && res.data.code == 1) {
								t.demoBalance = res.data.data.virtualmoney || '10000.00';
								uni.setStorageSync('guest_virtualmoney', t.demoBalance);
							}
						}
					});
				} else {
					// 没有device_id，使用本地存储的虚拟余额
					t.demoBalance = uni.getStorageSync('guest_virtualmoney') || '10000.00';
				}
				return;
			}

			t.req({
				url: "user/balance",
				data: {},
				success: function(res) {
					if (res.code == 1 && res.data) {
						t.accountBalance = res.data.money || '0.00';
						t.demoBalance = res.data.virtualmoney || '0.00';
					}
				},
				fail: function(err) {
					console.error('获取余额失败:', err);
				}
			});
		},
        // 获取默认图标
		getDefaultIcon(symbol) {
		if (!symbol) return '/static/img/coins/btc.png';
		// 从symbol中提取币种名称（如 BTCUSDT -> btc）
		const coinName = symbol; 
		//console.log(coinName);
		return `/static/img/coins/${coinName}.png`;
		},
		// 切换交易模式
		toggleTradeMode() {
			// 如果要切换到真实交易，需要先检查登录状态
			if (!this.isRealTrade && !this.userId) {
				uni.showModal({
					title: this.$t('提示'),
					content: this.$t('请先登录'),
					confirmText: this.$t('登录'),
					cancelText: this.$t('取消'),
					success: (res) => {
						if (res.confirm) {
							uni.navigateTo({
								url: '/pages/login/index'
							});
						}
					}
				});
				return;
			}

			// 已登录用户切换交易模式需要确认
			const targetMode = this.isRealTrade ? this.$t('模拟交易') : this.$t('真实交易');
			const confirmMessage = this.isRealTrade
				? this.$t('确定要切换到模拟交易吗？模拟交易使用虚拟资金，不会产生真实盈亏。')
				: this.$t('确定要切换到真实交易吗？真实交易将使用您的账户资金，请谨慎操作。');

			uni.showModal({
				title: this.$t('切换交易模式'),
				content: confirmMessage,
				confirmText: this.$t('确定'),
				cancelText: this.$t('取消'),
				success: (res) => {
					if (res.confirm) {
						this.isRealTrade = !this.isRealTrade;
						// 记录用户偏好，下次进入页面时自动使用该模式
						uni.setStorageSync('tradeMode', this.isRealTrade ? 'real' : 'demo');
						uni.showToast({
							title: this.isRealTrade ? this.$t('已切换到真实交易') : this.$t('已切换到模拟交易'),
							icon: 'none'
						});
						// 重新加载当前模式的委托订单
						this.loadPendingOrders();
					}
				}
			});
		},

		// 加载交易对列表
		loadTradePairList(options) {

			const t = this;
			t.req({
				url: "tradePair/list",
				data: {},
				success: function(res) {

					if (res.code == 1 && res.data && res.data.list) {
						t.tradePairList = res.data.list;

						// 确定默认选中的交易对
						let defaultPair = null;

						// 优先检查 storage 中的 selectedTradeCode（从首页跳转）
						const selectedCode = uni.getStorageSync('selectedTradeCode');
						if (selectedCode) {
							defaultPair = t.tradePairList.find(p => p.code === selectedCode);
							// 清除存储的code
							uni.removeStorageSync('selectedTradeCode');
						}

						// 其次检查 options.code（URL参数）
						if (!defaultPair && options && options.code) {
							defaultPair = t.tradePairList.find(p => p.code === options.code);
						}

						// 最后默认选择第一个
						if (!defaultPair && t.tradePairList.length > 0) {
							defaultPair = t.tradePairList[0];
						}

						if (defaultPair) {
							t.selectTradePair(defaultPair, true);
						}
					}
				},
				fail: function(err) {
					console.error('获取交易对列表失败:', err);
					// 失败时使用默认值
					t.coinId = 'bitcoin';
					t.tradingSymbol = 'BTCUSDT';
					t.loadCoinData();
					// 加载余额和订单（包括游客模式）
					t.loadBalance();
					t.loadPendingOrders();
				}
			});
		},

		// 切换交易对下拉框显示
		togglePairDropdown() {
			this.showMorePeriods = false;
			this.showPairPopup = !this.showPairPopup;
		},

		// 切换更多周期下拉框显示
		toggleMorePeriods() {
			this.showPairPopup = false;
			this.showMorePeriods = !this.showMorePeriods;
		},

		// 关闭所有下拉框
		closeAllDropdowns() {
			this.showPairPopup = false;
			this.showMorePeriods = false;
		},

		// 选择更多周期中的选项
		selectMorePeriod(item) {
			this.selectedPeriod = item.value;
			this.chartInterval = this.periodToInterval(item.value);
			this.showMorePeriods = false;
			//console.log('Selected more period:', item.label, '-> interval:', this.chartInterval);
			// 图表数据更新后，重新渲染价格标记（参考切换时区的处理）
			this.$nextTick(() => {
				// 先清空旧的标记数据，否则会认为标记已存在而跳过渲染
				if (this.$refs.chart && this.$refs.chart.clearMarkers) {
					this.$refs.chart.clearMarkers();
				}
				this.renderPendingOrderMarkers();
			});
		},

		// 选择交易对
		selectTradePair(pair, isInit = false) {
			this.currentPair = pair;
			this.showPairPopup = false;

			// 更新交易相关数据
			this.tradingSymbol = pair.code; // 如 BTCUSDT
			this.coinId = this.getCoinIdFromSymbol(pair.symbol.toLowerCase());

			// 更新 coin 对象基本信息
			this.coin.symbol = pair.symbol;
			this.coin.name = pair.realname;

			if (!isInit) {
				// 非初始化时显示提示
				uni.showToast({
					title: `${this.$t('已切换到')} ${pair.name}`,
					icon: 'none'
				});
			}

			// 加载相关数据
			this.loadCoinData();
			// 加载余额和订单（包括游客模式）
			this.loadBalance();
			this.loadPendingOrders();
		},

		// 加载当前委托订单（显示所有币种的订单）
		loadPendingOrders() {
			const t = this;

			// 如果是模拟交易且没有登录，使用游客接口
			if (!t.isRealTrade && !t.userId) {
				const deviceId = uni.getStorageSync('device_id');
				if (!deviceId) {
					
					console.log('无设备ID，跳过加载委托订单');
					return;
				}

				uni.request({
					url: t.siteBaseUrl + 'tradeOrder/pendingOrdersGuest',
					method: 'POST',
					data: {
						device_id: deviceId
						// 不传 coin_id，获取所有订单
					},
					success: function(res) {
						const result = res.data;
						if (result.code == 1 && result.data) {
							// 更新余额
							if (result.data.virtualmoney) {
								t.demoBalance = result.data.virtualmoney;
								uni.setStorageSync('guest_virtualmoney', t.demoBalance);
							}

							// 合并订单数据，保留现有订单的倒计时状态
							t.mergeOrders(result.data.orders || []);
						}
					},
					fail: function(err) {
						console.error('获取游客委托订单失败:', err);
					}
				});
				return;
			}

			t.req({
				url: "tradeOrder/pendingOrders",
				data: {
					// 不传 coin_id，获取所有订单
					money_type: t.isRealTrade ? 1 : 2
				},
				success: function(res) {
					if (res.code == 1 && res.data) {
						// 合并订单数据，保留现有订单的倒计时状态
						t.mergeOrders(res.data.orders || []);
					}
				},
				fail: function(err) {
					console.error('获取委托订单失败:', err);
				}
			});
		},

		/**
		 * 将日期字符串转换为时间戳（秒）
		 * 支持格式: "2025-12-22 15:16:47"
		 */
		parseDateToTimestamp(dateStr) {
			if (!dateStr) return null;
			// 如果已经是数字（时间戳），直接返回
			if (typeof dateStr === 'number') return dateStr;
			try {
				// 将 "2025-12-22 15:16:47" 格式转换为时间戳
				const timestamp = new Date(dateStr.replace(/-/g, '/')).getTime();
				return Math.floor(timestamp / 1000);
			} catch (e) {
				console.error('日期解析失败:', dateStr, e);
				return null;
			}
		},

		/**
		 * 合并订单数据，保留现有订单的倒计时状态
		 * 解决问题：刷新订单时不会覆盖正在倒计时的订单状态
		 */
		mergeOrders(serverOrders) {
			const t = this;

			// 创建现有订单的ID映射，用于快速查找
			const existingOrderMap = {};
			t.pendingOrders.forEach(order => {
				existingOrderMap[order.id] = order;
			});

			// 构建新的订单列表
			const newPendingOrders = serverOrders.map(order => {
				const existingOrder = existingOrderMap[order.id];

				if (existingOrder) {
					// 订单已存在，保留前端的倒计时和动画状态
					// 只更新不影响倒计时/动画的字段
					// 确保 entry_time 存在（兼容旧订单）
					const entryTime = existingOrder.entry_time || t.parseDateToTimestamp(order.created_at) || Math.floor(Date.now() / 1000);
					return {
						...existingOrder,
						// 这些字段可以从服务器更新
						amount: parseFloat(order.amount),
						entry_price: parseFloat(order.entry_price),
						profit_rate: parseFloat(order.profit_rate),
						coin_symbol: order.coin_symbol || existingOrder.coin_symbol,
						entry_time: entryTime // 确保有时间戳
						// remainingSeconds / playingEgg 保留前端的值，不从服务器覆盖
					};
				} else {
					// 新订单，使用服务器数据，并初始化动画状态
					// 将 created_at 日期字符串转换为时间戳
					const entryTime = t.parseDateToTimestamp(order.created_at) || Math.floor(Date.now() / 1000);
					return {
						id: order.id,
						totalSeconds: order.total_seconds,
						remainingSeconds: order.remaining_seconds,
						type: order.trade_type,
						amount: parseFloat(order.amount),
						entry_price: parseFloat(order.entry_price),
						profit_rate: parseFloat(order.profit_rate),
						coin_symbol: order.coin_symbol || '',
						playingEgg: false,
						entry_time: entryTime // 保存订单创建时间戳，用于周期切换时正确定位K线
					};
				}
			});

			// 更新订单列表
			t.pendingOrders = newPendingOrders;

			// 如果有订单且定时器未运行，启动定时器
			if (t.pendingOrders.length > 0 && !t.orderTimer) {
				t.startOrderTimer();
			}

			// 刷新图表上的订单标记
			t.renderPendingOrderMarkers();
		},

		// 更新TradingView交易对符号
		updateTradingSymbol() {
			// 获取币种符号，TradingView通常使用BINANCE交易所数据
			const symbol = this.coin && this.coin.symbol ? this.coin.symbol.toUpperCase() : 'BTC';
			
			// 设置TradingView交易对格式 (例如: BTCUSDT)
			this.tradingSymbol = `${symbol}USDT`;
			
			//console.log('Updated TradingView symbol:', this.tradingSymbol);
		},
		
		// 图表准备就绪回调
		onChartReady(chart) {
			//console.log('Lightweight chart is ready');
			this.chartLoading = false;
		},
		
		// 图表错误回调
		onChartError(error) {
			console.error('Chart error:', error);
			uni.showToast({
				title: this.$t('图表加载失败'),
				icon: 'none'
			});
			this.chartLoading = false;
		},
		
		// 价格更新回调
		onPriceUpdate(price) {
			// 更新实时价格显示
			if (price && this.coin) {
				this.coin.current_price = price;
			}
			//console.log('Price updated:', price);
		},
		
		// 数据加载完成回调
		onDataLoaded(data) {
			//console.log('Historical data loaded:', data.length, 'candles');
			// 图表数据加载完成后，渲染未结算订单的标记
			this.renderPendingOrderMarkers();
		},

		// 图表重建完成回调（切换时区时触发）
		onChartRebuilt() {
			//console.log('图表已重建，重新渲染订单标记');
			// 图表重建后，重新渲染未结算订单的标记
			this.renderPendingOrderMarkers();
		},

		// 渲染所有未结算订单的价格标记
		renderPendingOrderMarkers() {
			const t = this;
			// 检查图表组件和订单列表是否存在
			if (!t.$refs.chart || !t.pendingOrders || t.pendingOrders.length === 0) {
				console.log('[renderPendingOrderMarkers] 无订单或图表未就绪');
				return;
			}

			// 获取当前交易对的币种符号（用于过滤）
			const currentCoinSymbol = t.currentPair.symbol ? t.currentPair.symbol.toUpperCase() : (t.coin.symbol ? t.coin.symbol.toUpperCase() : '');
			console.log('[renderPendingOrderMarkers] 当前交易对币种:', currentCoinSymbol, '订单总数:', t.pendingOrders.length);

			// 获取图表组件中已存在的订单标记信息
			const existingOrderMarkerInfo = t.$refs.chart.orderMarkerInfo || {};

			// 遍历所有未结算订单，只为当前交易对且没有标记的订单添加标记
			t.pendingOrders.forEach((order, index) => {
				if (order.entry_price && order.id) {
					// 过滤：只显示当前交易对的订单标记
					const orderCoinSymbol = order.coin_symbol ? order.coin_symbol.toUpperCase() : '';
					if (orderCoinSymbol && currentCoinSymbol && orderCoinSymbol !== currentCoinSymbol) {
						// 订单不属于当前交易对，跳过
						return;
					}

					// 检查该订单是否已经有标记
					if (existingOrderMarkerInfo[order.id]) {
						console.log('[renderPendingOrderMarkers] 订单标记已存在，跳过:', order.id);
						return;
					}

					// 订单没有标记，添加新标记
					// 尝试获取订单的时间戳（可能在不同字段中）
					const orderTime = order.entry_time || order.created_at || order.timestamp;

					if (order.type === 'up') {
						t.$refs.chart.addBuyUpMarker(order.entry_price, order.id, orderTime);
					} else if (order.type === 'down') {
						t.$refs.chart.addBuyDownMarker(order.entry_price, order.id, orderTime);
					}
				} else {
					console.warn('[renderPendingOrderMarkers] ✗ 订单缺少必要字段:', order);
				}
			});

		},

		// 确认交易（直接执行，无需确认弹框）
		confirmTrade(type) {
			// 检查是否选择了时间
			if (!this.selectedTradeTime) {
				uni.showToast({
					title: this.$t('请选择交易时间'),
					icon: 'none'
				});
				return;
			}

			// 检查是否选择了金额
			if (!this.selectedAmount || this.selectedAmount <= 0) {
				uni.showToast({
					title: this.$t('请选择交易金额'),
					icon: 'none'
				});
				return;
			}

			// 真实交易需要登录，模拟交易不需要
			if (this.isRealTrade && !this.userId) {
				uni.showModal({
					title: this.$t('提示'),
					content: this.$t('请先登录'),
					confirmText: this.$t('登录'),
					cancelText: this.$t('取消'),
					success: (res) => {
						if (res.confirm) {
							uni.navigateTo({
								url: '/pages/login/index'
							});
						}
					}
				});
				return;
			}

			if (!this.currentPair || !this.currentPair.id) {
				uni.showToast({
					title: this.$t('请选择交易对'),
					icon: 'none'
				});
				return;
			}

			// 直接执行交易，无需确认弹框
			this.executeTrade(type, this.selectedAmount);
		},

		// 执行交易
		executeTrade(type, amount) {
			const t = this;

			// 检查是否选择了交易对
			if (!t.currentPair || !t.currentPair.id) {
				uni.showToast({
					title: t.$t('请选择交易对'),
					icon: 'none'
				});
				return;
			}

			// 检查余额（根据交易模式）
			const currentBalance = t.isRealTrade ? parseFloat(t.accountBalance) : parseFloat(t.demoBalance);

			uni.showLoading({
				title: t.$t('提交中')
			});

			// 从当前选中的交易对获取数据
			// 注意：当前价格由服务端获取，不再使用前端价格，防止篡改
			const coinSymbol = t.currentPair.symbol || t.currentPair.name.split('/')[0];

			// 获取当前选中的交易时间配置
			const selectedTimeOption = t.tradeTimeOptions.find(opt => opt.value === t.selectedTradeTime);
			const totalSeconds = selectedTimeOption ? selectedTimeOption.seconds : 60;
			// 获取赔率（去掉百分号，转为小数）
			const profitRateStr = selectedTimeOption ? selectedTimeOption.rate : '92%';
			const profitRate = parseFloat(profitRateStr.replace('%', '')) / 100;

			// 如果是模拟交易且没有登录，使用游客下单接口
			if (!t.isRealTrade && !t.userId) {
				const deviceId = uni.getStorageSync('device_id');
				if (!deviceId) {
					uni.hideLoading();
					uni.showToast({
						title: t.$t('请刷新页面重试'),
						icon: 'none'
					});
					return;
				}

				// 调用游客下单接口
				uni.request({
					url: t.siteBaseUrl + 'tradeOrder/placeOrderGuest',
					method: 'POST',
					data: {
						device_id: deviceId,
						coin_id: t.currentPair.id,
						trading_symbol: t.currentPair.code,
						coin_symbol: coinSymbol.toUpperCase(),
						trade_type: type,
						amount: amount,
						trade_time: t.selectedTradeTime
						// entry_price 由服务端获取，不再从前端传递
					},
					success: function(res) {
						uni.hideLoading();
						const result = res.data;

						if (result.code == 1) {
							const orderId = result.data.order_id;
							// 使用服务端返回的入场价格
							const entryPrice = result.data.entry_price;

							// 在图表上添加标记（使用服务端返回的价格）
							if (t.$refs.chart && entryPrice) {
								if (type === 'up') {
									t.$refs.chart.addBuyUpMarker(entryPrice, orderId);
								} else {
									t.$refs.chart.addBuyDownMarker(entryPrice, orderId);
								}
							}

			// 添加新订单到待处理列表
						const newOrder = {
							id: orderId,
							totalSeconds: totalSeconds,
							remainingSeconds: result.data.remaining_seconds || totalSeconds,
							type: type,
							amount: amount,
							entry_price: entryPrice,
							profit_rate: result.data.profit_rate,
							coin_symbol: coinSymbol.toUpperCase(),
							playingEgg: false,
							entry_time: Math.floor(Date.now() / 1000) // 保存下单时间戳，用于周期切换时正确定位K线
						};
							t.pendingOrders.push(newOrder);

							// 启动定时器
							if (!t.orderTimer) {
								t.startOrderTimer();
							}

							// 更新余额
							t.demoBalance = result.data.virtualmoney || t.demoBalance;
							uni.setStorageSync('guest_virtualmoney', t.demoBalance);

							// 重置选择状态
							t.selectedTradeTime = '';
							t.selectedAmount = null;

							uni.showToast({
								title: `${type === 'up' ? t.$t('买涨') : t.$t('买跌')}${t.$t('订单已提交')}`,
								icon: 'success'
							});
						} else {
							uni.showToast({
								title: result.msg || t.$t('下单失败'),
								icon: 'none'
							});
						}
					},
					fail: function(err) {
						uni.hideLoading();
						uni.showToast({
							title: t.$t('网络请求失败'),
							icon: 'none'
						});
					}
				});
				return;
			}

			// 调用下单接口（已登录用户）
			t.req({
				url: "tradeOrder/placeOrder",
				method: "POST",
				data: {
					coin_id: t.currentPair.id,
					trading_symbol: t.currentPair.code,
					coin_symbol: coinSymbol.toUpperCase(),
					trade_type: type,
					amount: amount,
					trade_time: t.selectedTradeTime,
					trade_seconds: totalSeconds,
					profit_rate: profitRate,
					// entry_price 由服务端获取，不再从前端传递
					money_type: t.isRealTrade ? 1 : 2
				},
				success: function(res) {
					uni.hideLoading();

					if (res.code == 1) {
						const orderId = res.data.order_id;
						// 使用服务端返回的入场价格
						const entryPrice = res.data.entry_price;

						// 在图表上添加标记（使用服务端返回的价格）
						if (t.$refs.chart && entryPrice) {
							if (type === 'up') {
								t.$refs.chart.addBuyUpMarker(entryPrice, orderId);
							} else {
								t.$refs.chart.addBuyDownMarker(entryPrice, orderId);
							}
						}

						// 添加订单到待处理列表
						const newOrder = {
							id: orderId,
							totalSeconds: totalSeconds,
							remainingSeconds: res.data.remaining_seconds || totalSeconds,
							type: type,
							amount: amount,
							entry_price: entryPrice,
							profit_rate: res.data.profit_rate,
							coin_symbol: coinSymbol.toUpperCase(),
							playingEgg: false,
							entry_time: Math.floor(Date.now() / 1000) // 保存下单时间戳，用于周期切换时正确定位K线
						};
						t.pendingOrders.push(newOrder);

						// 启动定时器
						if (!t.orderTimer) {
							t.startOrderTimer();
						}

						// 更新余额
						t.loadBalance();

						// 重置选择状态
						t.selectedTradeTime = '';
						t.selectedAmount = null;

						uni.showToast({
							title: `${type === 'up' ? t.$t('买涨') : t.$t('买跌')}${t.$t('订单已提交')}`,
							icon: 'success'
						});
					} else {
						uni.showToast({
							title: res.msg || t.$t('下单失败'),
							icon: 'none'
						});
					}
				},
				fail: function(err) {
					uni.hideLoading();
					uni.showToast({
						title: t.$t('网络请求失败'),
						icon: 'none'
					});
				}
			});
		},
		
		// 执行买入操作
		executeBuy() {
			if (!this.userId) {
				uni.showToast({
					title: this.$t('请先登录'),
					icon: 'none'
				});
				return;
			}
			
			// 验证交易金额
			const amount = parseFloat(this.tradeAmount);
			if (isNaN(amount) || amount <= 0) {
				uni.showToast({
					title: this.$t('请输入有效的交易金额'),
					icon: 'none'
				});
				return;
			}
			
			// 防止重复点击
			if (this.isProcessing) return;
			this.isProcessing = true;
			
			uni.showLoading({
				title: this.$t('处理中...')
			});
			
			this.req({
				url: "bot/buy",
				method: "POST",
				data: {
					coin_id: this.coinId,
					coin_symbol: this.coin.symbol,
					amount: amount,
					price: this.coin.current_price,
					userid: this.userId
				},
				success: (res) => {
					if (res.code == 1) {
						uni.showToast({
							title: this.$t('买入成功'),
							icon: 'success'
						});
						
						// 重新加载持仓数据
						//this.loadHoldingAmount();
						
						// 清空输入框
						this.tradeAmount = '';
					} else {
						uni.showToast({
							title: res.msg || this.$t('买入失败'),
							icon: 'none'
						});
					}
				},
				fail: (err) => {
					uni.showToast({
						title: this.$t('网络请求失败，请重试'),
						icon: 'none'
					});
				},
				complete: () => {
					uni.hideLoading();
					this.isProcessing = false;
				}
			});
		},
		
		// 执行卖出操作
		executeSell() {
			if (!this.userId) {
				uni.showToast({
					title: this.$t('请先登录'),
					icon: 'none'
				});
				return;
			}
			
			// 检查是否有持仓
			if (this.holdingAmount <= 0) {
				uni.showToast({
					title: this.$t('没有可卖出的持仓'),
					icon: 'none'
				});
				return;
			}
			
			// 验证交易金额
			const amount = parseFloat(this.tradeAmount);
			if (isNaN(amount) || amount <= 0) {
				uni.showToast({
					title: this.$t('请输入有效的交易金额'),
					icon: 'none'
				});
				return;
			}
			
			// 检查是否超过持仓USDT量
			if (amount > this.holdingUsdtAmount) {
				uni.showToast({
					title: this.$t('不能超过持仓量'),
					icon: 'none'
				});
				return;
			}
			
			// 防止重复点击
			if (this.isProcessing) return;
			this.isProcessing = true;
			
			uni.showModal({
				title: this.$t('确认卖出'),
				content: `${this.$t('确定要卖出')} ${amount} USDT 吗？`,
				success: (res) => {
					if (res.confirm) {
						uni.showLoading({
							title: this.$t('处理中...')
						});
						
						this.req({
							url: "bot/sell",
							method: "POST",
							data: {
								coin_id: this.coinId,
								coin_symbol: this.coin.symbol,
								amount: amount,
								price: this.coin.current_price,
								userid: this.userId
							},
							success: (res) => {
								if (res.code == 1) {
									uni.showToast({
										title: this.$t('卖出成功'),
										icon: 'success'
									});
									
									// 重新加载持仓数据
									//this.loadHoldingAmount();
									
									// 清空输入框
									this.tradeAmount = '';
								} else {
									uni.showToast({
										title: res.msg || this.$t('卖出失败'),
										icon: 'none'
									});
								}
							},
							fail: (err) => {
								uni.showToast({
									title: this.$t('网络请求失败，请重试'),
									icon: 'none'
								});
							},
							complete: () => {
								uni.hideLoading();
								this.isProcessing = false;
							}
						});
					} else {
						this.isProcessing = false;
					}
				}
			});
		},
		
		// 格式化价格
		formatPrice(price) {
			if (!price) return '0.00';
			
			// 针对不同价格范围使用不同的小数位数
			if (price < 0.01) {
				return price.toFixed(6);
			} else if (price < 1) {
				return price.toFixed(4);
			} else if (price < 10000) {
				return price.toFixed(2);
			} else {
				return Math.round(price).toLocaleString();
			}
		},
		
		// 格式化大数字（例如市值）
		formatLargeNumber(num) {
			if (!num) return '0';
			
			if (num >= 1000000000) {
				return (num / 1000000000).toFixed(2) + 'B';
			} else if (num >= 1000000) {
				return (num / 1000000).toFixed(2) + 'M';
			} else if (num >= 1000) {
				return (num / 1000).toFixed(2) + 'K';
			} else {
				return num.toFixed(2);
			}
		},
		
		// 计算价格区间百分比
		calculateRangePercentage() {
			if (!this.coin.low_24h || !this.coin.high_24h || !this.coin.current_price) return 50;
			
			const range = this.coin.high_24h - this.coin.low_24h;
			if (range === 0) return 50;
			
			const percentage = ((this.coin.current_price - this.coin.low_24h) / range) * 100;
			return Math.min(Math.max(percentage, 0), 100);
		},
		
		// 返回上一页
		goBack() {
			uni.navigateBack({
				delta: 1,
				fail: () => {
					// 如果返回失败，则尝试跳转到市场页
					uni.switchTab({
						url: '/pages/market/market'
					});
				}
			});
		},
		
		// 处理图像加载错误
		handleImageError() {
			console.error(this.$t('图像加载错误，尝试使用备用图标'));
			// 设置为一个通用的加密货币图标
			this.coin.image = 'https://cryptologos.cc/logos/question-mark.png';
		},
		
		// 获取币种图像URL
		getCoinImageUrl() {
			if (this.coin && this.coin.image) {
				// 检查 API 返回的图像 URL
				if (this.isValidImageUrl(this.coin.image)) {
					return this.coin.image;
				}
			}
			
			// 尝试使用 CoinIcons API
			if (this.coin && this.coin.symbol) {
				const symbol = this.coin.symbol.toLowerCase();
				// 返回 Cryptocompare 图标 API (常用且可靠)
				return `https://cryptocompare.com/media/37746238/${symbol}.png`;
			}
			
			// 尝试使用 CryptoIcons
			if (this.coin && this.coin.symbol) {
				const symbol = this.coin.symbol.toLowerCase();
				return `https://cryptoicons.org/api/icon/${symbol}/200`;
			}
			
			// 默认图标
			return 'https://cryptologos.cc/logos/question-mark.png';
		},
		
		// 新增：选择时间
		selectTime(index) {
			this.selectedTimeIndex = index;
		},
		
		// 新增：选择交易时间
		selectTradeTime(value) {
			this.selectedTradeTime = value;
		},
		
		// 新增：选择金额
		selectAmount(amount) {
			this.selectedAmount = amount;
			this.tradeAmount = amount.toString();
		},
		
		// 新增：输入数字
		inputNumber(num) {
			if (this.tradeAmount === '') {
				this.tradeAmount = num.toString();
			} else {
				this.tradeAmount += num.toString();
			}
		},
		
		// 新增：删除数字
		deleteNumber() {
			if (this.tradeAmount.length > 0) {
				this.tradeAmount = this.tradeAmount.slice(0, -1);
			}
		},

		// 前往历史记录页面
		goToHistory() {
			uni.navigateTo({
				url: '/pages/market/history',
				fail: () => {
					uni.showToast({
						title: this.$t('历史记录页面开发中'),
						icon: 'none'
					});
				}
			});
		},

		// 新增：选择时间周期
		selectPeriod(period) {
			this.selectedPeriod = period;
			this.showMorePeriods = false;
			// 将周期映射到图表 interval
			this.chartInterval = this.periodToInterval(period);
			console.log('Selected period:', period, '-> interval:', this.chartInterval);
			// 图表数据更新后，重新渲染价格标记（参考切换时区的处理）
			this.$nextTick(() => {
				// 先清空旧的标记数据，否则会认为标记已存在而跳过渲染
				if (this.$refs.chart && this.$refs.chart.clearMarkers) {
					this.$refs.chart.clearMarkers();
				}
				this.renderPendingOrderMarkers();
			});
		},

		// 将时间周期映射到 Binance K线 interval
		periodToInterval(period) {
			const mapping = {
				// 大写格式（UI显示）
				'1M': '1m',    // 1分钟K线
				'2M': '2m',    // 2分钟K线（注：Binance可能不支持2m，会fallback到1m）
				'3M': '3m',    // 3分钟K线
				'5M': '5m',    // 5分钟K线
				'10M': '15m',  // 10分钟K线（注：Binance不支持10m，使用15m）
				'15M': '15m',  // 15分钟K线
				'30M': '30m',  // 30分钟K线
				'1H': '1h',    // 1小时K线
				'4H': '4h',    // 4小时K线
				// 小写格式（moreTimePeriods 的 value）
				'30m': '30m',
				'1h': '1h',
				'4h': '4h'
			};
			return mapping[period] || '1m';
		},

		// 格式化倒计时显示
		formatCountdown(seconds) {
			const mins = Math.floor(seconds / 60);
			const secs = seconds % 60;
			return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
		},

		// 获取圆形进度条样式
		getProgressStyle(order) {
			const progress = (order.remainingSeconds / order.totalSeconds) * 100;
			const degree = (progress / 100) * 360;
			return {
				background: `conic-gradient(#F9D54A ${degree}deg, #2C2C2C ${degree}deg)`
			};
		},

		// 启动订单倒计时
		startOrderTimer() {
			const t = this;
			if (t.orderTimer) {
				clearInterval(t.orderTimer);
			}
			t.orderTimer = setInterval(() => {
				// 遍历所有订单，单独处理每个订单的倒计时
				t.pendingOrders.forEach(order => {
					if (order.remainingSeconds > 0) {
						order.remainingSeconds--;

						// 当订单倒计时结束时，开始轮询检查该订单状态，并触发金蛋爆炸动画
						if (order.remainingSeconds <= 0 && !order.isChecking) {
							order.remainingSeconds = 0;
							console.log('订单倒计时结束，开始检查状态:', order.id);
							order.isChecking = true; // 标记正在检查，避免重复轮询
							order.playingEgg = true; // 停止抖动效果
							t.checkAndSettleOrder(order); // 检查结果后会调用 playEggAnimation
						}
					}
				});

				// 只有当所有订单都已完结（从列表移除）时才停止定时器
				if (t.pendingOrders.length === 0) {
					t.stopOrderTimer();
				}
			}, 1000);
		},

		/**
		 * 检查并结算单个订单
		 * 轮询后端直到订单真正完结
		 */
		checkAndSettleOrder(order) {
			const t = this;
			const orderId = order.id;
			const maxRetries = 30; // 最多重试30次（30秒）
			let retryCount = 0;

			const checkStatus = () => {
				// 构建请求
				const isGuest = !t.isRealTrade && !t.userId;
				const deviceId = uni.getStorageSync('device_id');

				let requestConfig;
				if (isGuest) {
					requestConfig = {
						url: t.siteBaseUrl + 'tradeOrder/checkOrderStatus',
						method: 'POST',
						data: {
							device_id: deviceId,
							order_id: orderId
						}
					};
				} else {
					requestConfig = {
						url: t.siteBaseUrl + 'tradeOrder/checkOrderStatus',
						method: 'POST',
						header: {
							'Authorization': 'Bearer ' + uni.getStorageSync('token')
						},
						data: {
							order_id: orderId
						}
					};
				}

				uni.request({
					...requestConfig,
					success: function(res) {
						const result = res.data;
						//console.log('订单状态检查结果:', orderId, result);

						if (result.code == 1 && result.data) {
							const status = result.data.status;

							if (status == 2 || status == 3) {
								// 订单已完结，根据结果显示不同动画
								const orderResult = result.data.result;
								const currentOrder = t.pendingOrders.find(o => o.id === orderId);
								if (currentOrder) {
									// 根据结果设置 eggResult: win/lose/tie
									if (orderResult == 1 || orderResult === 'win') {
										currentOrder.eggResult = 'win';
									} else if (orderResult == 0 || orderResult === 'lose') {
										currentOrder.eggResult = 'lose';
									} else {
										currentOrder.eggResult = 'draw';
									}
									// 播放结果动画后再移除订单
									t.playEggAnimation(currentOrder, () => {
										t.removeOrderFromList(orderId);
										t.loadBalance();
									});
								} else {
									t.loadBalance();
								}
							} else {
								// 订单未完结，继续轮询
								retryCount++;
								if (retryCount < maxRetries) {
									//console.log('订单未完结，继续轮询:', orderId, '重试次数:', retryCount);
									setTimeout(checkStatus, 1000);
								} else {
									// 超过最大重试次数，强制移除并刷新
									//console.log('订单轮询超时，强制刷新:', orderId);
									t.removeOrderFromList(orderId);
									t.loadBalance();
								}
							}
						} else {
							// 请求失败或订单不存在，重试或移除
							retryCount++;
							if (retryCount < maxRetries) {
								setTimeout(checkStatus, 1000);
							} else {
								t.removeOrderFromList(orderId);
								t.loadBalance();
							}
						}
					},
					fail: function(err) {
						console.error('检查订单状态失败:', orderId, err);
						retryCount++;
						if (retryCount < maxRetries) {
							setTimeout(checkStatus, 1000);
						} else {
							t.removeOrderFromList(orderId);
							t.loadBalance();
						}
					}
				});
			};

			// 开始第一次检查
			checkStatus();
		},

		/**
		 * 从订单列表中移除指定订单
		 */
		removeOrderFromList(orderId) {
			const t = this;
			const index = t.pendingOrders.findIndex(o => o.id === orderId);
			if (index > -1) {
				t.pendingOrders.splice(index, 1);
				// 移除图表标记
				if (t.$refs.chart) {
					t.$refs.chart.removeOrderMarker(orderId);
				}
			}
		},

			// 停止订单倒计时
			stopOrderTimer() {
				if (this.orderTimer) {
					clearInterval(this.orderTimer);
					this.orderTimer = null;
				}
			},

			// 根据订单结果返回对应的金蛋图片路径
			getEggImageSrc(order) {
				if (!order.eggResult) {
					// 未有结果时显示静态金蛋
					return '/static/img/market/detail/egg.png';
				}
				if (order.eggResult === 'win') {
					return '/static/img/market/detail/eggbob.gif';
				}
				if (order.eggResult === 'lose') {
					console.log(order.eggResult);
					return '/static/img/market/detail/egglose.gif';
				}
				// 平局情况不会显示（已在模板中v-if过滤）
				return '/static/img/market/detail/egg.png';
			},

			// 播放金蛋结果动画：在原位置显示结果GIF，结束后执行回调
			playEggAnimation(order, callback) {
				if (!order) {
					if (callback) callback();
					return;
				}

				// 如果是平局，直接消失
				if (order.eggResult === 'draw') {
					order.playingEgg = false;
					if (callback) callback();
					return;
				}

				// 赢或输的情况，播放对应GIF动画后移除
				setTimeout(() => {
					order.playingEgg = false;
					if (callback) callback();
				}, 1800); // GIF播放1.5秒后移除
			},

		// 添加新订单
		addPendingOrder(type, duration) {
			const newOrder = {
				id: Date.now(),
				totalSeconds: duration,
				remainingSeconds: duration,
				type: type // 'up' 或 'down'
			};
			this.pendingOrders.push(newOrder);

			// 如果定时器没有运行，启动它
			if (!this.orderTimer) {
				this.startOrderTimer();
			}
		},

		// 打开小键盘
		openKeyboard(type) {
			this.keyboardType = type;
			this.keyboardInput = '';
			this.showKeyboard = true;
		},

		// 关闭小键盘
		closeKeyboard() {
			this.showKeyboard = false;
			this.keyboardInput = '';
		},

		// 键盘按键输入
		onKeyPress(key) {
			// 限制输入长度
			if (this.keyboardInput.length < 6) {
				this.keyboardInput += key;
			}
		},

		// 键盘删除
		onKeyDelete() {
			if (this.keyboardInput.length > 0) {
				this.keyboardInput = this.keyboardInput.slice(0, -1);
			}
		},

		// 键盘确认
		onKeyConfirm() {
			const value = parseInt(this.keyboardInput) || 0;

			if (value <= 0) {
				uni.showToast({
					title: this.$t('请输入有效数值'),
					icon: 'none'
				});
				return;
			}

			if (this.keyboardType === 'time') {
				// 更新时间，转换为显示格式
				this.selectedTradeTime = value + 'S';
			} else if (this.keyboardType === 'amount') {
				// 更新金额
				this.selectedAmount = value;
				this.tradeAmount = value.toString();
			}

			this.closeKeyboard();
		},

		// 初始化游客用户
		initGuestUser() {
			const t = this;

			// 生成或获取设备ID
			let deviceId = uni.getStorageSync('device_id');
			if (!deviceId) {
				// 生成唯一设备ID
				deviceId = 'device_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
				uni.setStorageSync('device_id', deviceId);
			}

			// 调用后端接口初始化游客用户
			uni.request({
				url: t.siteBaseUrl + 'user/initGuest',
				method: 'POST',
				data: { device_id: deviceId },
				success: function(res) {
					if (res.data && res.data.code == 1) {
						const data = res.data.data;
						// 保存游客信息
						uni.setStorageSync('guest_userid', data.userid);
						uni.setStorageSync('guest_virtualmoney', data.virtualmoney);
						t.demoBalance = data.virtualmoney || '10000.00';
						console.log('游客用户初始化成功:', data);
					} else {
						console.error('游客用户初始化失败:', res.data);
					}
				},
				fail: function(err) {
					console.error('游客用户初始化请求失败:', err);
				}
			});
		}
	}
}
</script>

<style>
page {
	height: 100%;
	overflow: hidden;
}

.coin-detail-container {
	background-color: #0E0F0F;
	height: calc(100vh - 100rpx - env(safe-area-inset-bottom));
	padding: 0;
	display: flex;
	flex-direction: column;
	overflow: hidden;
	box-sizing: border-box;
}
 

.status-time {
	font-size: 28rpx;
	color: #FFFFFF;
	font-weight: 600;
}

.status-icons {
	display: flex;
	gap: 16rpx;
}

.status-icon {
	font-size: 28rpx;
}

/* 币种头部信息 */
.coin-header {
	border-radius: 12rpx;
	margin: 10rpx 20rpx 0rpx 20rpx;
	flex-shrink: 0;
}

/* 币种标题栏 */
.coin-title-bar {
	display: flex;
	background:#1A1A1A;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 24rpx;
	padding: 20rpx 30rpx;
}

.back-icon {
	width: 48rpx;
	height: 48rpx;
	cursor: pointer;
}

.coin-title-text {
	font-size: 36rpx;
	font-weight: bold;
	color: #FFFFFF;
	flex: 1;
	text-align: center;
}

.log-icon {
	width: 56rpx;
	height: 56rpx;
	cursor: pointer;
}

.history-column {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	padding: 8rpx 12rpx;
}

.history-text {
	font-size: 20rpx;
	color: #999999;
	margin-top: 4rpx;
}

/* 账户余额卡片 */
.balance-cards {
	display: flex;
	gap: 10rpx;
	margin-bottom: 10rpx;
}

.balance-card { 
	background-color: #1C180E;
	border-radius: 22rpx;
	border: 2px solid #292D2E;
	padding: 10rpx;
	display: flex;
	gap: 10rpx;
	align-items: center;
}
.balance-card1 {
	flex: 1;
	background: linear-gradient(135deg, #F15A4A 0%, #3E0707 100%);
	border-radius: 22rpx; 
	padding: 10rpx;
	display: flex;
	gap: 10rpx;
	align-items: center;
	position: relative;
	overflow: hidden;
}

.balance-card1::before {
	content: '';
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	border-radius: 22rpx;
	padding: 2px;
	background: linear-gradient(135deg, #F99696 0%, #B81616 100%);
	-webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
	-webkit-mask-composite: xor;
	mask-composite: exclude;
	pointer-events: none;
}

/* 真实交易模式 - 绿色渐变 */
.balance-card1.real-trade-mode {
	background: linear-gradient(135deg, #26a69a 0%, #0a3d2e 100%);
}

.balance-card1.real-trade-mode::before {
	background: linear-gradient(135deg, #6fd9b5 0%, #16b881 100%);
}

.trade-mode-switch {
	cursor: pointer;
	justify-content: center;
}

.switch-content {
	display: flex; 
	justify-content: center;
	align-items: center;
	gap: 8rpx;
}

.switch-icon {
	padding-left: 3px;
	width: 35rpx;
	height: 35rpx;
}

.switch-text {
	margin-left:5rpx;
	font-size: 22rpx;
	color: white; 
	letter-spacing: 1px;
}
.card-icon-wrapper { 
	border-radius: 12rpx;
	display: flex;
	align-items: center;
	justify-content: center;
}

.card-icon {
	font-size: 32rpx;
}

.card-icon-img {
	width: 48rpx;
	height: 48rpx;
}

.card-info {
	display: flex;
	flex-direction: column;
	gap: 6rpx; 
	margin-left:5rpx;
	
}

.card-label {
	font-size: 22rpx;
	color: #999;
}

.card-value {
	font-size: 28rpx;
	font-weight: 600;
	color: #FFFFFF;
}

/* 价格显示区域 */
.price-display {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 20rpx;
	border-radius: 12rpx;
}

.price-left-section {
	display: flex;
	flex-direction: column;
	gap: 12rpx;
	flex: 1;
}

.price-top-row {
	display: flex;
	align-items: center;
	gap: 12rpx;
}

.coin-icon {
	width: 40rpx;
	height: 40rpx;
}

.price-pair {
	font-size: 28rpx;
	color: #FFFFFF;
	font-weight: 500;
}

.price-value {
	font-size: 40rpx;
	font-weight: bold;
	color: #FFFFFF;
}

.change-badge {
	display: flex;
	align-items: center;
	gap: 6rpx;
	padding: 6rpx 16rpx;
	background-color: rgba(52, 199, 89, 0.15);
	border-radius: 20rpx;
}

.change-arrow {
	width: 20rpx;
	height: 20rpx;
}

.change-text {
	font-size: 24rpx;
	font-weight: 600;
}

.change-text.positive {
	color: #33D49D;
}

.change-text.negative {
	color: #FF6464;
}

.kline-icon {
	width: 64rpx;
	height: 64rpx;
}

/* 时间周期选择器 */
/* 交易对选择器 */
.pair-selector-wrapper {
	position: relative;
	z-index: 100;
}

.pair-selector {
	display: flex;
	align-items: center;
	gap: 8rpx;
	padding: 16rpx 16rpx;
	border: 1px solid #2C2C2C;
	border-radius: 8rpx;
	margin-right: 10rpx;
	cursor: pointer;
}

.pair-text {
	font-size: 24rpx;
	color: #FFFFFF;
	font-weight: 500;
}

/* 交易对下拉框 */
.pair-dropdown {
	position: absolute;
	top: 100%;
	left: 0;
	margin-top: 8rpx;
	min-width: 280rpx;
	background-color: #1A1A1A;
	border: 1px solid #2C2C2C;
	border-radius: 12rpx;
	box-shadow: 0 8rpx 24rpx rgba(0, 0, 0, 0.4);
	z-index: 1001;
	overflow: hidden;
}

.pair-dropdown-list {
	max-height: 400rpx;
}

.pair-dropdown-item {
	display: flex;
	align-items: center;
	padding: 16rpx 20rpx;
	gap: 12rpx;
	transition: background-color 0.2s;
}

.pair-dropdown-item:active {
	background-color: #2C2C2C;
}

.pair-item-active {
	background-color: rgba(249, 213, 74, 0.15);
}

.pair-item-icon {
	width: 40rpx;
	height: 40rpx;
	border-radius: 50%;
	flex-shrink: 0;
}

.pair-item-info {
	flex: 1;
	display: flex;
	flex-direction: column;
}

.pair-item-name {
	font-size: 24rpx;
	color: #FFFFFF;
	font-weight: 500;
}

.pair-item-check-icon {
	font-size: 24rpx;
	color: #F9D54A;
	font-weight: 600;
}

/* 下拉框遮罩层 */
.pair-dropdown-overlay {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	z-index: 99;
	background-color: transparent;
}

/* 更多周期下拉框 */
.period-more-wrapper {
	position: relative;
	z-index: 100;
}

.period-more {
	min-width: 70rpx;
}

.period-dropdown {
	position: absolute;
	top: 100%;
	right: 0;
	margin-top: 8rpx;
	min-width: 120rpx;
	background-color: #1A1A1A;
	border: 1px solid #2C2C2C;
	border-radius: 12rpx;
	box-shadow: 0 8rpx 24rpx rgba(0, 0, 0, 0.4);
	z-index: 1001;
	overflow: hidden;
}

.period-dropdown-item {
	padding: 16rpx 20rpx;
	text-align: center;
	transition: background-color 0.2s;
}

.period-dropdown-item:active {
	background-color: #2C2C2C;
}

.period-dropdown-item.period-item-active {
	background-color: rgba(74, 135, 249, 0.3);
}

.period-dropdown-text {
	font-size: 24rpx;
	color: #FFFFFF;
	font-weight: 500;
}

.period-item-active .period-dropdown-text {
	color: #FFFFFF;
}

.time-period-selector {
	display: flex;
	gap: 10rpx; 
	margin: 10rpx 20rpx 10rpx 20rpx;
	border-radius: 12rpx;
	align-items: center;
	flex-shrink: 0;
}

.period-item { 
	padding: 12rpx 20rpx;
	text-align: center;
	border-radius: 8rpx;
	transition: all 0.3s;
	border:1px solid #2C2C2C;
}

.period-active {
	background-color:rgb(74, 135, 249);
}

.period-text {
	font-size: 26rpx;
	color: #999;
	font-weight: 500;
}

.period-active .period-text {
	color: #FFFFFF;
	font-weight: 600;
}

/* 图表容器 */
.chart-container {
	background-color: #2C2C2C;
	border-radius: 12rpx;
	padding: 0;
	margin: 0 20rpx 10rpx 20rpx;
	position: relative;
	overflow: hidden;
	flex: 1;
	min-height: 0;
	display: flex;
	flex-direction: column;
}

.chart-price-label {
	position: absolute;
	top: 20rpx;
	left: 20rpx;
	z-index: 10;
	background-color: rgba(0, 0, 0, 0.6);
	padding: 8rpx 16rpx;
	border-radius: 8rpx;
}

.chart-price-text {
	font-size: 28rpx;
	font-weight: 600;
	color: #FFFFFF;
}

.trading-view-wrapper {
	position: relative;
	width: 100%;
	flex: 1;
	min-height: 100px;
	overflow: hidden;
	background-color: #1A1A1A;
}

/* 时间刻度 */
.time-scale {
	display: flex;
	justify-content: space-between;
	padding: 20rpx 40rpx;
	background-color: #2C2C2C;
	margin: 0 20rpx 20rpx 20rpx;
	border-radius: 12rpx;
}

.time-scale-item {
	font-size: 22rpx;
	color: #999;
}

/* 当前委托订单 */
.order-section {
	background: linear-gradient(to bottom, #212222, #0E0F0F);
	border-radius: 12rpx;
	padding: 10rpx 20rpx;
	margin: 10rpx 20rpx 0rpx 20rpx;
	border: 2px solid #292D2E;
	overflow: visible; /* 允许 GIF 动画溢出 */
}

.countdown-timers {
	width: 100%;
	height: 120rpx;
	white-space: nowrap;
	overflow: visible; /* 允许溢出 */
}

.countdown-timers-inner {
	display: inline-flex;
	gap: 20rpx;
	overflow: visible;
}

.timer-item {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 8rpx;
	flex-shrink: 0;
	overflow: visible;
}

/* 金蛋倒计时容器 */
.egg-timer {
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 4rpx;
	overflow: visible;
}

.egg-wrapper {
	position: relative;
	/* 控制整体尺寸，使倒计时和币种名称都能完整展示 */
	width: 90rpx;
	height: 110rpx;
	flex-shrink: 0;
	overflow: hidden; 
	
}

/* 显示结果 GIF 时允许溢出 */
.egg-wrapper-result {
	overflow: visible;
}

/* 金蛋图片：使用 PNG / GIF 作为 <image> 资源 */
.egg-static {
	width: 100%;
	height: 100%;  
	bottom:-4px;  
}

/* 结果 GIF 样式：放大并允许溢出显示完整动画 */
.egg-result-gif {
	width: 130%;
	height: 120%;  
	bottom:-4px;
	margin-left: -20%; 
	margin-top: -30%; 
}
.egg-result-gif1 {
	width: 130%;
	height: 120%;  
	bottom:-4px; 
	margin-left: -10%; 
	margin-top: -30%; 
}
/* 抖动效果（每隔几秒抖动一次） */
.egg-static-shake {
	animation: egg-shake 5s infinite;
	transform-origin: center bottom;
}

@keyframes egg-shake {
	0%, 80%, 100% {
		transform: translateX(0) rotate(0deg);
	}
	82% {
		transform: translateX(-4rpx) rotate(-3deg);
	}
	84% {
		transform: translateX(4rpx) rotate(3deg);
	}
	86% {
		transform: translateX(-3rpx) rotate(-2deg);
	}
	88% {
		transform: translateX(3rpx) rotate(2deg);
	}
	90% {
		transform: translateX(0) rotate(0deg);
	}
}

/* 倒计时居中覆盖在金蛋中间 */
.egg-countdown {
	position: absolute;
	top: 42%;
	left: 50%;
	transform: translate(-50%, -50%);
	width: 100%;
	text-align: center;
}

.egg-timer-value {
	font-size: 18rpx;
	color: #FFFFFF;
	font-weight: 600;
	text-shadow: 0 2rpx 4rpx rgba(0, 0, 0, 0.6);
}

/* 币种名称：移动到倒计时下面，位于金蛋底部区域 */
.egg-coin-label {
	position: absolute;
	bottom: 0rpx;
	left: 50%;
	transform: translateX(-50%);
	width: 100%;
	text-align: center;
}

.egg-coin-text {
	font-size: 16rpx;
	color: #FFD37A;
	font-weight: 500;
	text-shadow: 0 2rpx 4rpx rgba(0, 0, 0, 0.6);
}

.timer-label {
	font-size: 22rpx;
	color: #92979E;
}

/* Time 和 Amount 标签头部 */
.trade-labels-header {
	display: flex;
	flex-shrink: 0;
}

.label-header-text {
	font-size: 22rpx;
	width: 50%;
	color: #FFFFFF;
	font-weight: 500;
	padding: 6rpx 20rpx;
}

/* 交易选项网格 */
.trade-options-grid {
	display: flex;
	gap: 16rpx;
	padding: 0 20rpx;
	margin-bottom: 10rpx;
	flex-shrink: 0;
}

.time-selection-wrapper,
.amount-selection-wrapper {
	flex: 1;
	min-width: 0;
	display: flex;
	flex-direction: column;
	gap: 10rpx;
	overflow: hidden;
}

/* Time 网格滚动容器 */
.time-grid-scroll {
	width: 100%;
	white-space: nowrap;
	background: linear-gradient(to bottom, #212222, #0E0F0F);
	border-radius: 12rpx;
	overflow-x: auto;
	overflow-y: hidden;
	-webkit-overflow-scrolling: touch;
}

/* 隐藏滚动条 */
.time-grid-scroll::-webkit-scrollbar {
	display: none;
}

/* Time 网格 */
.time-grid {
	display: inline-flex;
	gap: 6rpx;
	padding: 10rpx;
}

.time-grid-item {
	flex-shrink: 0;
	min-width: 60rpx;
	padding: 8rpx 8rpx;
	border-radius: 8rpx;
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 2rpx;
	transition: all 0.3s;
}

.grid-item-active {
	background: #F9D54A;
}

.grid-item-label {
	font-size: 20rpx;
	color: rgba(255, 255, 255, 0.7);
	font-weight: 500;
}

.grid-item-active .grid-item-label {
	color: black;
}

.grid-item-rate {
	font-size: 16rpx;
	color: rgba(255, 255, 255, 0.5);
}

.grid-item-active .grid-item-rate {
	color: black;
}

/* Amount 网格 */
.amount-grid {
	display: flex;
	gap: 8rpx;
	background: linear-gradient(to bottom, #212222, #0E0F0F);
	padding: 10rpx;
	border-radius: 12rpx;
}

.amount-grid-item {
	flex: 1;
	padding: 8rpx 4rpx;
	border-radius: 8rpx;
	display: flex;
	align-items: center;
	flex-direction: column;
	justify-content: center;
	gap: 2rpx;
	transition: all 0.3s;
}

/* 选中显示 */
.selection-display {
	background-color: #2C2C2C;
	padding: 10rpx 16rpx;
	border-radius: 12rpx;
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.display-label {
	font-size: 22rpx;
	color: #999;
}

.display-value {
	font-size: 24rpx;
	color: #FFFFFF;
	font-weight: 600;
}


/* 买涨买跌按钮 */
.trade-buttons {
	display: flex;
	gap: 20rpx;
	padding: 16rpx 20rpx;
	background: #1A1A1A;
	flex-shrink: 0;
}

.trade-btn {
	flex: 1;
	height: 80rpx;
	border-radius: 16rpx;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	gap: 4rpx;
	cursor: pointer;
	transition: all 0.3s;
}

.buy-up {
	background: #33D49D;
	box-shadow: 0 8rpx 24rpx rgba(52, 199, 89, 0.3);
}

.buy-up:active {
	transform: scale(0.98);
	box-shadow: 0 4rpx 12rpx rgba(52, 199, 89, 0.3);
}

.buy-down {
	background: #FF6464;
	box-shadow: 0 8rpx 24rpx rgba(255, 59, 48, 0.3);
}

.buy-down:active {
	transform: scale(0.98);
	box-shadow: 0 4rpx 12rpx rgba(255, 59, 48, 0.3);
}

.btn-icon {
	font-size: 40rpx;
}

.btn-text {
	font-size: 28rpx;
	color: #FFFFFF;
	font-weight: 600;
}

/* 交易弹窗 */
.trade-modal {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: rgba(0, 0, 0, 0.7);
	display: flex;
	align-items: flex-end;
	z-index: 9999;
}

.modal-content {
	width: 100%;
	background: #2C2C2C;
	border-radius: 32rpx 32rpx 0 0;
	padding: 40rpx 30rpx;
	animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
	from {
		transform: translateY(100%);
	}
	to {
		transform: translateY(0);
	}
}

.modal-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 30rpx;
}

.modal-title {
	font-size: 36rpx;
	color: #FFFFFF;
	font-weight: 600;
}

.modal-close {
	font-size: 40rpx;
	color: #999;
	padding: 10rpx;
}

.modal-coin-info {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 24rpx;
	background: #1A1A1A;
	border-radius: 16rpx;
	margin-bottom: 30rpx;
}

.modal-coin-name {
	font-size: 32rpx;
	color: #FFFFFF;
	font-weight: 600;
}

.modal-coin-price {
	font-size: 32rpx;
	color: #34C759;
	font-weight: 600;
}

.modal-section {
	margin-bottom: 30rpx;
}

.section-label {
	font-size: 28rpx;
	color: #999;
	margin-bottom: 20rpx;
	display: block;
}

.time-options,
.amount-options {
	display: flex;
	gap: 16rpx;
	flex-wrap: wrap;
}

.option-item {
	flex: 1;
	min-width: 150rpx;
	padding: 24rpx 16rpx;
	background: #1A1A1A;
	border-radius: 12rpx;
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 8rpx;
	border: 2rpx solid transparent;
	transition: all 0.3s;
}

.option-active {
	background: linear-gradient(135deg, #2962ff 0%, #1e53e5 100%);
	border-color: #2962ff;
}

.option-label {
	font-size: 28rpx;
	color: #FFFFFF;
	font-weight: 500;
}

.option-rate {
	font-size: 24rpx;
	color: #999;
}

.option-active .option-rate {
	color: #F9D54A;
}

.option-text {
	font-size: 32rpx;
	color: #FFFFFF;
	font-weight: 600;
}

.custom-amount {
	margin-top: 20rpx;
	display: flex;
	align-items: center;
	gap: 16rpx;
	padding: 24rpx;
	background: #1A1A1A;
	border-radius: 12rpx;
}

.custom-label {
	font-size: 28rpx;
	color: #999;
	white-space: nowrap;
}

.custom-input {
	flex: 1;
	font-size: 32rpx;
	color: #FFFFFF;
	background: transparent;
	border: none;
	outline: none;
}

.modal-footer {
	margin-top: 40rpx;
}

.modal-confirm-btn {
	width: 100%;
	height: 100rpx;
	border-radius: 16rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	transition: all 0.3s;
}

.btn-up {
	background: linear-gradient(135deg, #34C759 0%, #30D158 100%);
	box-shadow: 0 8rpx 24rpx rgba(52, 199, 89, 0.3);
}

.btn-down {
	background: linear-gradient(135deg, #FF3B30 0%, #FF453A 100%);
	box-shadow: 0 8rpx 24rpx rgba(255, 59, 48, 0.3);
}

.modal-confirm-btn:active {
	transform: scale(0.98);
}

.confirm-btn-text {
	font-size: 32rpx;
	color: #FFFFFF;
	font-weight: 600;
}

/* 弹窗中的数字键盘 */
.keyboard-grid-modal {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 16rpx;
	margin-top: 20rpx;
}

.keyboard-key-modal {
	height: 80rpx;
	background: #1A1A1A;
	border-radius: 12rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	cursor: pointer;
	transition: all 0.2s;
}

.keyboard-key-modal:active {
	background: #363636;
	transform: scale(0.95);
}

.delete-key-modal {
	background: #FF3B30;
}

.delete-key-modal:active {
	background: #CC2F26;
}

.key-text-modal {
	font-size: 36rpx;
	color: #FFFFFF;
	font-weight: 500;
}

/* 弹窗中使用原有的 time-row 和 amount-row 样式 */
.modal-section .time-row,
.modal-section .amount-row {
	margin: 20rpx 0;
}

.modal-section .selected-info {
	width: 100%;
	display: flex;
	justify-content: space-between;
	padding: 20rpx 24rpx;
	background: #1A1A1A;
	border-radius: 12rpx;
	margin-top: 20rpx;
}

.modal-section .info-label {
	font-size: 28rpx;
	color: #999;
}

.modal-section .info-value {
	font-size: 28rpx;
	color: #FFFFFF;
	font-weight: 600;
}

.user-level {
		display: inline-block;
		background-color: rgba(255, 255, 255, 0.2);
		padding: 4rpx 16rpx;
		border-radius: 20rpx;
	}

/* 底部小键盘 */
.keyboard-overlay {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: rgba(0, 0, 0, 0.5);
	display: flex;
	align-items: flex-end;
	z-index: 9999;
}

.keyboard-container {
	width: 100%;
	background: #1A1A1A;
	border-radius: 24rpx 24rpx 0 0;
	padding: 30rpx 20rpx;
	animation: slideUp 0.3s ease-out;
}

.keyboard-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 30rpx;
	padding: 0 10rpx;
}

.keyboard-title {
	font-size: 28rpx;
	color: #999;
}

.keyboard-input-display {
	font-size: 36rpx;
	color: #FFFFFF;
	font-weight: 600;
}

.keyboard-grid {
	display: flex;
	flex-direction: column;
	gap: 16rpx;
}

.keyboard-row {
	display: flex;
	gap: 16rpx;
}

.keyboard-key {
	flex: 1;
	height: 90rpx;
	background: #2C2C2C;
	border-radius: 12rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	transition: all 0.2s;
}

.keyboard-key:active {
	background: #3C3C3C;
	transform: scale(0.95);
}

.keyboard-key text {
	font-size: 32rpx;
	color: #FFFFFF;
	font-weight: 500;
}

.key-delete {
	background: #FF6464;
}

.key-delete:active {
	background: #CC5050;
}

.key-confirm {
	background: #33D49D;
}

.key-confirm:active {
	background: #29AA7E;
}
</style>
