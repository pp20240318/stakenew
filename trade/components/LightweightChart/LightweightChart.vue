<template>
	<view class="lightweight-chart-container">
		<view v-if="loading" class="loading">
			<text class="loading-text">{{ loadingText || '加载图表中...' }}</text>
		</view>
		<!-- #ifdef H5 -->
		<view :id="containerId" class="chart-widget" :style="{ height: chartHeight }"></view>
		<!-- 时区显示（可点击切换） -->
		<view class="timezone-wrapper">
			<view class="timezone-label" @tap.stop="toggleTimezoneDropdown">
				<text class="timezone-text">{{ timezoneDisplay }}</text>
				<text class="timezone-arrow">{{ showTimezoneDropdown ? '▲' : '▼' }}</text>
			</view>
			<!-- 时区下拉框 -->
			<view v-if="showTimezoneDropdown" class="timezone-dropdown" @tap.stop>
				<scroll-view class="timezone-dropdown-list" scroll-y>
					<view
						v-for="(tz, index) in timezoneOptions"
						:key="index"
						:class="['timezone-dropdown-item', selectedTimezoneOffset === tz.offset ? 'timezone-item-active' : '']"
						@tap="selectTimezone(tz)"
					>
						<text class="timezone-dropdown-text">{{ tz.label }}</text>
						<text v-if="selectedTimezoneOffset === tz.offset" class="timezone-check-icon">✓</text>
					</view>
				</scroll-view>
			</view>
		</view>
		<!-- 点击遮罩关闭下拉框 -->
		<view v-if="showTimezoneDropdown" class="timezone-overlay" @tap="closeTimezoneDropdown"></view>
		<!-- #endif -->
		<!-- #ifndef H5 -->
		<view class="chart-fallback" :style="{ height: chartHeight }">
			<text class="fallback-text">图表功能仅在 H5 端可用</text>
		</view>
		<!-- #endif -->
	</view>
</template>

<script>
export default {
	name: 'LightweightChart',
	props: {
		symbol: {
			type: String,
			default: 'BTCUSDT'
		},
		interval: {
			type: String,
			default: '1m' // 1m, 5m, 15m, 1h, 4h, 1d
		},
		theme: {
			type: String,
			default: 'dark' // dark or light
		},
		height: {
			type: [Number, String],
			default: 500
		},
		limit: {
			type: Number,
			default: 1000 // 历史数据条数
		},
		autoConnect: {
			type: Boolean,
			default: true // 自动连接 WebSocket
		},
		loadingText: {
			type: String,
			default: ''
		}
	},
	data() {
		return {
			containerId: `lw_chart_${Date.now()}`,
			chart: null,
			candlestickSeries: null,
			ws: null, // K线数据 WebSocket
			loading: true,
			historicalData: [],
			latestPrice: 0,
			markers: [], // 存储所有标记
			priceLines: [], // 存储所有价格线
			isChangingInterval: false, // 是否正在切换周期
			reconnectTimer: null, // 重连定时器
			timezone: '', // 用户时区
			timezoneOffset: 0, // 时区偏移（秒）
			orderMarkers: {}, // 订单标记计数器 { time_type: count }，用于合并同一K线上的订单
			orderPriceLines: {}, // 订单价格线映射 { orderId: priceLine }
			orderMarkerInfo: {}, // 订单标记信息 { orderId: { time, type } }
			showTimezoneDropdown: false, // 是否显示时区下拉框
			selectedTimezoneOffset: null, // 用户选择的时区偏移（分钟），null表示使用本地时区
			// 常用时区列表
			timezoneOptions: [
				{ label: 'UTC-12:00 (Baker Island)', offset: 720 },
				{ label: 'UTC-11:00 (Samoa)', offset: 660 },
				{ label: 'UTC-10:00 (Hawaii)', offset: 600 },
				{ label: 'UTC-09:00 (Alaska)', offset: 540 },
				{ label: 'UTC-08:00 (Los Angeles)', offset: 480 },
				{ label: 'UTC-07:00 (Denver)', offset: 420 },
				{ label: 'UTC-06:00 (Chicago)', offset: 360 },
				{ label: 'UTC-05:00 (New York)', offset: 300 },
				{ label: 'UTC-04:00 (Santiago)', offset: 240 },
				{ label: 'UTC-03:00 (São Paulo)', offset: 180 },
				{ label: 'UTC-02:00 (Mid-Atlantic)', offset: 120 },
				{ label: 'UTC-01:00 (Azores)', offset: 60 },
				{ label: 'UTC+00:00 (London)', offset: 0 },
				{ label: 'UTC+01:00 (Paris)', offset: -60 },
				{ label: 'UTC+02:00 (Cairo)', offset: -120 },
				{ label: 'UTC+03:00 (Moscow)', offset: -180 },
				{ label: 'UTC+04:00 (Dubai)', offset: -240 },
				{ label: 'UTC+05:00 (Karachi)', offset: -300 },
				{ label: 'UTC+05:30 (Mumbai)', offset: -330 },
				{ label: 'UTC+06:00 (Dhaka)', offset: -360 },
				{ label: 'UTC+07:00 (Bangkok)', offset: -420 },
				{ label: 'UTC+08:00 (Singapore)', offset: -480 },
				{ label: 'UTC+09:00 (Tokyo)', offset: -540 },
				{ label: 'UTC+10:00 (Sydney)', offset: -600 },
				{ label: 'UTC+11:00 (Solomon Is.)', offset: -660 },
				{ label: 'UTC+12:00 (Auckland)', offset: -720 }
			]
		}
	},
	computed: {
		binanceSymbol() {
			return this.symbol.toUpperCase();
		},
		wsSymbol() {
			return this.symbol.toLowerCase();
		},
		chartHeight() {
			// 如果是数字，添加 'px'；如果是字符串（如 '100%'），直接返回
			if (typeof this.height === 'number') {
				return this.height + 'px';
			}
			return this.height;
		},
		chartHeightPx() {
			// 用于初始化图表时的高度（需要数字）
			if (typeof this.height === 'number') {
				return this.height;
			}
			// 如果是百分比，返回容器实际高度
			return null;
		},
		// 时区显示文字
		timezoneDisplay() {
			if (this.selectedTimezoneOffset !== null) {
				// 用户手动选择了时区
				return this.formatTimezoneOffset(this.selectedTimezoneOffset);
			}
			return this.timezone || this.getLocalTimezone();
		}
	},
	mounted() {
		// #ifdef H5
		// 初始化时区
		this.initTimezone();
		this.$nextTick(() => {
			setTimeout(() => {
				this.initChart();
			}, 100);
		});
		// #endif
	},
	beforeUnmount() {
		// #ifdef H5
		this.destroyChart();
		// #endif
	},
	methods: {
		// 初始化时区
		initTimezone() {
			// #ifdef H5
			try {
				// 先尝试加载用户保存的时区设置
				this.loadSavedTimezone();

				// 如果没有保存的设置，使用系统时区
				if (this.selectedTimezoneOffset === null) {
					// 获取时区偏移（分钟）
					const offsetMinutes = new Date().getTimezoneOffset();
					// 转换为秒
					this.timezoneOffset = -offsetMinutes * 60;

					// 获取时区名称
					const timezoneStr = Intl.DateTimeFormat().resolvedOptions().timeZone;

					// 计算 UTC 偏移显示
					const offsetHours = Math.abs(Math.floor(offsetMinutes / 60));
					const offsetMins = Math.abs(offsetMinutes % 60);
					const sign = offsetMinutes <= 0 ? '+' : '-';
					const utcOffset = `UTC${sign}${offsetHours.toString().padStart(2, '0')}:${offsetMins.toString().padStart(2, '0')}`;

					// 设置时区显示（如 "UTC+08:00"）
					this.timezone = `${utcOffset}`;

				}
				//console.log('[initTimezone] timezoneOffset:', this.timezoneOffset, 'selectedTimezoneOffset:', this.selectedTimezoneOffset);
			} catch (e) {
				console.error('[initTimezone] Error:', e);
				this.timezone = 'UTC+00:00';
				this.timezoneOffset = 0;
			}
			// #endif
		},

		// 获取本地时区（备用方法）
		getLocalTimezone() {
			const offsetMinutes = new Date().getTimezoneOffset();
			const offsetHours = Math.abs(Math.floor(offsetMinutes / 60));
			const offsetMins = Math.abs(offsetMinutes % 60);
			const sign = offsetMinutes <= 0 ? '+' : '-';
			return `UTC${sign}${offsetHours.toString().padStart(2, '0')}:${offsetMins.toString().padStart(2, '0')}`;
		},

		// 格式化时区偏移为显示文字
		formatTimezoneOffset(offsetMinutes) {
			const offsetHours = Math.abs(Math.floor(offsetMinutes / 60));
			const offsetMins = Math.abs(offsetMinutes % 60);
			const sign = offsetMinutes <= 0 ? '+' : '-';
			return `UTC${sign}${offsetHours.toString().padStart(2, '0')}:${offsetMins.toString().padStart(2, '0')}`;
		},

		// 根据用户选择的时区获取调整后的日期对象
		getAdjustedDate(timestampMs) {
			const date = new Date(timestampMs);
			// 计算时区偏移（毫秒）
			// timezoneOffset 是秒，正值表示东时区
			const offsetMs = this.timezoneOffset * 1000;
			// 创建调整后的日期（加上时区偏移）
			return new Date(date.getTime() + offsetMs);
		},

		// 切换时区下拉框显示
		toggleTimezoneDropdown() {
			this.showTimezoneDropdown = !this.showTimezoneDropdown;
		},

		// 关闭时区下拉框
		closeTimezoneDropdown() {
			this.showTimezoneDropdown = false;
		},

		// 选择时区
		selectTimezone(tz) {

			//console.log('[selectTimezone] 切换时区:', tz.label, 'offset:', tz.offset);
			this.selectedTimezoneOffset = tz.offset;
			// offset 是 getTimezoneOffset() 格式：UTC+8 返回 -480，UTC-12 返回 720
			// 我们需要转换为秒数偏移量：UTC+8 应该是 +28800 秒，UTC-12 应该是 -43200 秒
			this.timezoneOffset = -tz.offset * 60; // 转换为秒
			//console.log('[selectTimezone] 新的 timezoneOffset:', this.timezoneOffset);
			this.showTimezoneDropdown = false;

			// 保存到本地存储
			try {
				localStorage.setItem('chart_timezone_offset', tz.offset.toString());
			} catch (e) {
			}

			// 刷新图表时间轴以应用新时区
			this.refreshChartTimeScale();
		},

		// 刷新图表时间轴（通过重建图表实现）
		refreshChartTimeScale() { 
			// #ifdef H5
			if (this.chart && this.historicalData.length > 0) {
				// 保存当前可见范围
				const timeScale = this.chart.timeScale();
				const visibleRange = timeScale.getVisibleLogicalRange();

				// 保存历史数据
				const savedData = [...this.historicalData]; 

				// 销毁旧图表（但保留 WebSocket 连接）
				if (this.chart) {
					window.removeEventListener('resize', this.handleResize);
					this.chart.remove();
					this.chart = null;
					this.candlestickSeries = null;
				}

				// 重新初始化图表
				this.$nextTick(() => {
					this.rebuildChart(savedData, visibleRange);
				});
			} else {
				
			}
			// #endif
		},

		// 重建图表
		rebuildChart(savedData, visibleRange) {

			// #ifdef H5
			const container = document.getElementById(this.containerId);
			if (!container) {
				console.error('[rebuildChart] Chart container not found');
				return;
			}

			const themeColors = this.getThemeColors();
			const chartH = this.chartHeightPx || container.clientHeight || 300;

			// 清空订单标记相关的数据，否则会认为标记已存在而跳过渲染
			this.markers = [];
			this.priceLines = [];
			this.orderMarkers = {};
			this.orderPriceLines = {};
			this.orderMarkerInfo = {};
			//console.log('[rebuildChart] 已清空订单标记数据');

			// 保存 this 引用，确保在 localization 回调中能正确访问
			const self = this;
			//console.log('[rebuildChart] 重建图表，timezoneOffset:', this.timezoneOffset);

			this.chart = window.LightweightCharts.createChart(container, {
				width: container.clientWidth,
				height: chartH,
				layout: {
					background: { color: themeColors.background },
					textColor: themeColors.text,
				},
				grid: {
					vertLines: { color: themeColors.grid },
					horzLines: { color: themeColors.grid },
				},
				crosshair: {
					mode: window.LightweightCharts.CrosshairMode.Normal,
				},
				rightPriceScale: {
					borderColor: themeColors.border,
				},
				timeScale: {
					borderColor: themeColors.border,
					timeVisible: true,
					secondsVisible: false,
				},
				localization: {
					timeFormatter: (timestamp) => {
						// 数据的时间戳已经被 getAdjustedDataForDisplay 调整过了
						// 直接显示即可
						const date = new Date(timestamp * 1000);
						const hours = date.getUTCHours().toString().padStart(2, '0');
						const minutes = date.getUTCMinutes().toString().padStart(2, '0');
						return `${hours}:${minutes}`;
					},
					dateFormatter: (timestamp) => {
						// timestamp 是 UTC 时间戳（秒）
						const utcMs = timestamp * 1000;
						const localMs = utcMs + self.timezoneOffset * 1000;
						const localDate = new Date(localMs);
						const year = localDate.getUTCFullYear();
						const month = (localDate.getUTCMonth() + 1).toString().padStart(2, '0');
						const day = localDate.getUTCDate().toString().padStart(2, '0');
						return `${year}-${month}-${day}`;
					}
				}
			});

			this.candlestickSeries = this.chart.addCandlestickSeries({
				upColor: themeColors.up,
				downColor: themeColors.down,
				borderVisible: false,
				wickUpColor: themeColors.up,
				wickDownColor: themeColors.down,
			});

			// 保存原始数据
			this.historicalData = savedData;
			// 调整数据时间戳以匹配用户选择的时区
			const displayData = this.getAdjustedDataForDisplay(savedData);
			this.candlestickSeries.setData(displayData);

			// 恢复可见范围
			if (visibleRange) {
				this.chart.timeScale().setVisibleLogicalRange(visibleRange);
			}

			// 强制图表重新渲染时间轴标签
			// 通过轻微调整可见范围来触发时间轴重绘
			this.$nextTick(() => {
				if (this.chart) {
					const timeScale = this.chart.timeScale();
					const currentRange = timeScale.getVisibleLogicalRange();
					if (currentRange) {
						// 轻微调整后再恢复，触发重绘
						timeScale.setVisibleLogicalRange({
							from: currentRange.from - 0.01,
							to: currentRange.to + 0.01
						});
						setTimeout(() => {
							timeScale.setVisibleLogicalRange(currentRange);
						}, 10);
					}
				}
			});

			// 重新绑定 resize 事件
			window.addEventListener('resize', this.handleResize);

			// 触发事件通知父组件图表已重建
			this.$emit('chart-rebuilt');
			//console.log('[rebuildChart] 图表重建完成，触发 chart-rebuilt 事件');

			// #endif
		},

		// 从本地存储加载用户时区设置
		loadSavedTimezone() {
			try {
				const saved = localStorage.getItem('chart_timezone_offset');
				if (saved !== null) {
					const offset = parseInt(saved, 10);
					if (!isNaN(offset)) {
						this.selectedTimezoneOffset = offset;
						this.timezoneOffset = -offset * 60;
						
					}
				}
			} catch (e) {
				
			}
		},

		// 初始化图表
		initChart() {
			// #ifdef H5
			if (typeof window.LightweightCharts === 'undefined') {
				console.error('Lightweight Charts library not loaded');
				this.loading = false;
				this.$emit('error', 'Lightweight Charts library not loaded');
				return;
			}

			try {
				const container = document.getElementById(this.containerId);
				if (!container) {
					console.error('Chart container not found');
					return;
				}

				const themeColors = this.getThemeColors();

				// 计算图表高度
				const chartH = this.chartHeightPx || container.clientHeight || 300;

				// 保存 this 引用，确保在 localization 回调中能正确访问
				const self = this;
				//console.log('[initChart] 创建图表，timezoneOffset:', this.timezoneOffset);

				this.chart = window.LightweightCharts.createChart(container, {
					width: container.clientWidth,
					height: chartH,
					layout: {
						background: { color: themeColors.background },
						textColor: themeColors.text,
					},
					grid: {
						vertLines: { color: themeColors.grid },
						horzLines: { color: themeColors.grid },
					},
					crosshair: {
						mode: window.LightweightCharts.CrosshairMode.Normal,
					},
					rightPriceScale: {
						borderColor: themeColors.border,
					},
					timeScale: {
						borderColor: themeColors.border,
						timeVisible: true,
						secondsVisible: false,
					},
					localization: {
						timeFormatter: (timestamp) => {
							// 数据的时间戳已经被 getAdjustedDataForDisplay 调整过了
							// 直接显示即可
							const date = new Date(timestamp * 1000);
							const hours = date.getUTCHours().toString().padStart(2, '0');
							const minutes = date.getUTCMinutes().toString().padStart(2, '0');
							return `${hours}:${minutes}`;
						},
						dateFormatter: (timestamp) => {
							// timestamp 是 UTC 时间戳（秒）
							const utcMs = timestamp * 1000;
							const localMs = utcMs + self.timezoneOffset * 1000;
							const localDate = new Date(localMs);
							const year = localDate.getUTCFullYear();
							const month = (localDate.getUTCMonth() + 1).toString().padStart(2, '0');
							const day = localDate.getUTCDate().toString().padStart(2, '0');
							return `${year}-${month}-${day}`;
						}
					}
				});

				this.candlestickSeries = this.chart.addCandlestickSeries({
					upColor: themeColors.up,
					downColor: themeColors.down,
					borderVisible: false,
					wickUpColor: themeColors.up,
					wickDownColor: themeColors.down,
				});

				// 响应式调整
				window.addEventListener('resize', this.handleResize);

				// 加载数据
				this.loadData();

				this.$emit('ready', this.chart);
			} catch (error) {
				console.error('Error initializing chart:', error);
				this.loading = false;
				this.$emit('error', error);
			}
			// #endif
		},

		// 获取主题颜色
		getThemeColors() {
			if (this.theme === 'light') {
				return {
					background: '#FFFFFF',
					text: '#191919',
					grid: '#E1E3E6',
					border: '#D1D4DC',
					up: '#26a69a',
					down: '#ef5350'
				};
			}
			// dark theme
			return {
				background: '#1e222d',
				text: '#d1d4dc',
				grid: '#2a2e39',
				border: '#2a2e39',
				up: '#26a69a',
				down: '#ef5350'
			};
		},

		// 加载数据
		async loadData() {
			// #ifdef H5
			try {
				await this.fetchHistoricalData();
				if (this.autoConnect) {
					this.connectWebSocket();
				}
			} catch (error) {
				console.error('Error loading data:', error);
				this.$emit('error', error);
			}
			// #endif
		},

		// 获取时区调整后的数据（用于显示）
		getAdjustedDataForDisplay(data) {
			// 直接使用 timezoneOffset 来调整时间戳
			const adjustOffset = this.timezoneOffset;

			//console.log('[getAdjustedDataForDisplay] timezoneOffset:', this.timezoneOffset, 'adjustOffset:', adjustOffset);

			// 如果不需要调整，直接返回原数据
			if (adjustOffset === 0) {
				return data;
			}

			// 调整数据的时间戳
			const adjustedData = data.map(item => ({
				...item,
				time: item.time + adjustOffset
			}));

			//console.log('[getAdjustedDataForDisplay] 原始时间:', data[0]?.time, '→ 调整后:', adjustedData[0]?.time);
			return adjustedData;
		},

		// 获取历史数据
		async fetchHistoricalData() {
			// #ifdef H5
			try {
				

				const url = `https://fapi.binance.com/fapi/v1/klines?symbol=${this.binanceSymbol}&interval=${this.interval}&limit=${this.limit}`;
				const response = await fetch(url);

				if (!response.ok) {
					throw new Error(`HTTP error! status: ${response.status}`);
				}

				const data = await response.json();

				const formattedData = data.map(d => ({
					time: d[0] / 1000, // 转换为秒（UTC时间戳）
					open: parseFloat(d[1]),
					high: parseFloat(d[2]),
					low: parseFloat(d[3]),
					close: parseFloat(d[4]),
				}));



				// 保存原始UTC数据
				this.historicalData = formattedData;


				if (this.candlestickSeries) {
					// 调整数据时间戳以匹配用户选择的时区
					const displayData = this.getAdjustedDataForDisplay(formattedData);
					this.candlestickSeries.setData(displayData);
				} else {

				}

				if (formattedData.length > 0) {
					this.latestPrice = formattedData[formattedData.length - 1].close;
					this.$emit('price-update', this.latestPrice);
				}

				this.loading = false;
				this.$emit('data-loaded', formattedData);
				
				
			} catch (error) {
				
				this.loading = false;
				this.$emit('error', error);
				uni.showToast({
					title: '数据加载失败',
					icon: 'none'
				});
			}
			// #endif
		},

		// 连接 K线 WebSocket
		connectWebSocket() {
			// #ifdef H5
			console.log('[KlineWS] 连接 K线 WebSocket:', this.wsSymbol, '@', this.interval);

			// 清除之前的重连定时器
			if (this.reconnectTimer) {
				clearTimeout(this.reconnectTimer);
				this.reconnectTimer = null;
			}

			// 关闭旧连接（移除所有事件监听器避免触发重连）
			if (this.ws) {
				console.log('[KlineWS] 关闭旧的 K线 WebSocket');
				this.ws.onopen = null;
				this.ws.onmessage = null;
				this.ws.onerror = null;
				this.ws.onclose = null;
				this.ws.close();
				this.ws = null;
			}

			this.ws = new WebSocket(`wss://fstream.binance.com/ws/${this.wsSymbol}@kline_${this.interval}`);

			this.ws.onopen = () => {
				console.log('[KlineWS] K线 WebSocket 连接成功:', this.wsSymbol, '@', this.interval);
				// 连接成功后重置切换周期标志
				this.isChangingInterval = false;
				this.$emit('ws-connected');
			};

			this.ws.onmessage = (event) => {
				const data = JSON.parse(event.data);
				const kline = data.k;

				if (!kline) return;

				// 原始UTC时间戳的K线数据
				const candlestick = {
					time: kline.t / 1000,
					open: parseFloat(kline.o),
					high: parseFloat(kline.h),
					low: parseFloat(kline.l),
					close: parseFloat(kline.c),
				};

				if (this.candlestickSeries) {
					// 调整数据时间戳以匹配用户选择的时区
					const adjustedData = this.getAdjustedDataForDisplay([candlestick]);
					this.candlestickSeries.update(adjustedData[0]);
				}

				// 更新实时价格
				this.latestPrice = candlestick.close;
				this.$emit('price-update', this.latestPrice);

				// 更新历史数据（保存原始UTC时间戳）
				const existingIndex = this.historicalData.findIndex(d => d.time === candlestick.time);
				if (existingIndex !== -1) {
					this.historicalData[existingIndex] = candlestick;
				} else {
					this.historicalData.push(candlestick);
				}
			};

			this.ws.onerror = (error) => {
				console.error('WebSocket 错误:', error);
				this.$emit('ws-error', error);
			};

			this.ws.onclose = () => {
				console.log('[KlineWS] K线 WebSocket 连接关闭');
				this.$emit('ws-closed');

				// 5秒后重连（但如果正在切换周期则不重连）
				if (this.autoConnect && !this.isChangingInterval) {
					console.log('[KlineWS] 5秒后自动重连...');
					// 清除旧的定时器
					if (this.reconnectTimer) {
						clearTimeout(this.reconnectTimer);
					}
					this.reconnectTimer = setTimeout(() => {
						this.reconnectTimer = null;
						this.connectWebSocket();
					}, 5000);
				}
			};
			// #endif
		},

		// 添加价格线
		addPriceLine(price, options = {}) {
			// #ifdef H5
			if (!this.candlestickSeries) return null;

			const defaultOptions = {
				price: price,
				color: '#2962ff',
				lineWidth: 2,
				lineStyle: window.LightweightCharts.LineStyle.Solid,
				axisLabelVisible: true,
				title: '价格线',
			};

			const priceLine = this.candlestickSeries.createPriceLine({
				...defaultOptions,
				...options
			});

			return priceLine;
			// #endif
		},

		// 添加标记
		addMarker(time, options = {}) {
			// #ifdef H5
			if (!this.candlestickSeries) return;

			const defaultOptions = {
				time: time,
				position: 'belowBar',
				color: '#2962ff',
				shape: 'arrowUp',
				text: 'M',
				size: 2
			};

			const marker = {
				...defaultOptions,
				...options
			};

			this.markers.push(marker);
			this.candlestickSeries.setMarkers(this.markers);
			// #endif
		},
		
		// 获取当前K线的时间戳（根据当前周期对齐）
		getCurrentCandleTime() {
			const now = Math.floor(Date.now() / 1000);
			// 根据当前周期计算K线开始时间
			const intervalSeconds = this.getIntervalSeconds();
			return Math.floor(now / intervalSeconds) * intervalSeconds;
		},

		// 获取当前周期的秒数
		getIntervalSeconds() {
			const mapping = {
				'1m': 60,
				'2m': 120,
				'3m': 180,
				'5m': 300,
				'10m': 600,
				'15m': 900,
				'30m': 1800,
				'1h': 3600,
				'4h': 14400
			};
			return mapping[this.interval] || 60;
		},

		// 将时间戳对齐到当前周期的K线开始时间
		alignTimeToInterval(timestamp) {
			const intervalSeconds = this.getIntervalSeconds();
			return Math.floor(timestamp / intervalSeconds) * intervalSeconds;
		},

		// 添加买涨标记（U + 实心横线）
		addBuyUpMarker(price, orderId = null, time = null) {
			// #ifdef H5
			if (!this.candlestickSeries) return;

			// 保存原始时间戳（用于周期切换后重新计算）
			const originalTime = time || Math.floor(Date.now() / 1000);
			// 调整时间戳以匹配图表数据的时区（图表数据已经被 getAdjustedDataForDisplay 调整过）
			const adjustedTime = originalTime + this.timezoneOffset;
			// 使用调整后的时间戳对齐到K线开始时间
			const markerTime = this.alignTimeToInterval(adjustedTime);
			const markerKey = `${markerTime}_up`;

			//console.log('[addBuyUpMarker] 原始时间:', originalTime, '→ 调整后:', adjustedTime, '→ 对齐后:', markerTime, 'timezoneOffset:', this.timezoneOffset);

			// 增加计数器
			if (!this.orderMarkers[markerKey]) {
				this.orderMarkers[markerKey] = 0;
			}
			this.orderMarkers[markerKey]++;

			// 保存订单标记信息（包含原始时间戳）
			if (orderId) {
				this.orderMarkerInfo[orderId] = { time: markerTime, type: 'up', key: markerKey, originalTime: originalTime };
			}

			// 更新标记文字
			const count = this.orderMarkers[markerKey];
			const markerText = count > 1 ? `U*${count}` : 'U';

			// 查找是否已存在该时间的买涨标记
			const existingIndex = this.markers.findIndex(m => m.time === markerTime && m.position === 'aboveBar' && m.color === '#26a69a');

			if (existingIndex !== -1) {
				// 更新已存在的标记
				this.markers[existingIndex].text = markerText;
			} else {
				// 添加新标记 - U放在K线上方
				this.markers.push({
					time: markerTime,
					position: 'aboveBar',
					color: '#26a69a', // 绿色
					shape: 'text',
					text: markerText,
					size: 1
				});
			}

			// 按时间排序后更新标记
			this.markers.sort((a, b) => a.time - b.time);
			this.candlestickSeries.setMarkers(this.markers);

			// 添加价格线（实心细线）
			const priceLine = this.addPriceLine(price, {
				color: '#26a69a',
				lineWidth: 1,
				lineStyle: window.LightweightCharts.LineStyle.Solid,
				axisLabelVisible: true,
				title: ''
			});

			if (priceLine) {
				this.priceLines.push(priceLine);
				// 保存订单与价格线的关联
				if (orderId) {
					this.orderPriceLines[orderId] = priceLine;
				}
			}


			// #endif
		},

		// 添加买跌标记（D + 实心横线）
		addBuyDownMarker(price, orderId = null, time = null) {
			// #ifdef H5
			if (!this.candlestickSeries) return;

			// 保存原始时间戳（用于周期切换后重新计算）
			const originalTime = time || Math.floor(Date.now() / 1000);
			// 调整时间戳以匹配图表数据的时区（图表数据已经被 getAdjustedDataForDisplay 调整过）
			const adjustedTime = originalTime + this.timezoneOffset;
			// 使用调整后的时间戳对齐到K线开始时间
			const markerTime = this.alignTimeToInterval(adjustedTime);
			const markerKey = `${markerTime}_down`;

			//console.log('[addBuyDownMarker] 原始时间:', originalTime, '→ 调整后:', adjustedTime, '→ 对齐后:', markerTime, 'timezoneOffset:', this.timezoneOffset);

			// 增加计数器
			if (!this.orderMarkers[markerKey]) {
				this.orderMarkers[markerKey] = 0;
			}
			this.orderMarkers[markerKey]++;

			// 保存订单标记信息（包含原始时间戳）
			if (orderId) {
				this.orderMarkerInfo[orderId] = { time: markerTime, type: 'down', key: markerKey, originalTime: originalTime };
			}

			// 更新标记文字
			const count = this.orderMarkers[markerKey];
			const markerText = count > 1 ? `D*${count}` : 'D';

			// 查找是否已存在该时间的买跌标记
			const existingIndex = this.markers.findIndex(m => m.time === markerTime && m.position === 'belowBar' && m.color === '#ef5350');

			if (existingIndex !== -1) {
				// 更新已存在的标记
				this.markers[existingIndex].text = markerText;
			} else {
				// 添加新标记 - D放在K线下方
				this.markers.push({
					time: markerTime,
					position: 'belowBar',
					color: '#ef5350', // 红色
					shape: 'text',
					text: markerText,
					size: 1
				});
			}

			// 按时间排序后更新标记
			this.markers.sort((a, b) => a.time - b.time);
			this.candlestickSeries.setMarkers(this.markers);

			// 添加价格线（实心细线）
			const priceLine = this.addPriceLine(price, {
				color: '#ef5350',
				lineWidth: 1,
				lineStyle: window.LightweightCharts.LineStyle.Solid,
				axisLabelVisible: true,
				title: ''
			});

			if (priceLine) {
				this.priceLines.push(priceLine);
				// 保存订单与价格线的关联
				if (orderId) {
					this.orderPriceLines[orderId] = priceLine;
				}
			}


			// #endif
		},

		// 移除订单标记
		removeOrderMarker(orderId) {
			// #ifdef H5
			if (!this.candlestickSeries) return;

			const markerInfo = this.orderMarkerInfo[orderId];
			if (!markerInfo) {
				
				return;
			}

			const { time, type, key } = markerInfo;

			// 减少计数器
			if (this.orderMarkers[key]) {
				this.orderMarkers[key]--;

				// 更新或移除标记
				const position = type === 'up' ? 'aboveBar' : 'belowBar';
				const color = type === 'up' ? '#26a69a' : '#ef5350';
				const existingIndex = this.markers.findIndex(m => m.time === time && m.position === position && m.color === color);

				if (existingIndex !== -1) {
					const count = this.orderMarkers[key];
					if (count <= 0) {
						// 移除标记
						this.markers.splice(existingIndex, 1);
						delete this.orderMarkers[key];
					} else {
						// 更新标记文字
						const prefix = type === 'up' ? 'U' : 'D';
						this.markers[existingIndex].text = count > 1 ? `${prefix}*${count}` : prefix;
					}
					this.candlestickSeries.setMarkers(this.markers);
				}
			}

			// 移除价格线
			const priceLine = this.orderPriceLines[orderId];
			if (priceLine && this.candlestickSeries) {
				try {
					this.candlestickSeries.removePriceLine(priceLine);
					// 从 priceLines 数组中移除
					const lineIndex = this.priceLines.indexOf(priceLine);
					if (lineIndex !== -1) {
						this.priceLines.splice(lineIndex, 1);
					}
				} catch (e) {
					
				}
				delete this.orderPriceLines[orderId];
			}

			// 清理订单标记信息
			delete this.orderMarkerInfo[orderId];


			// #endif
		},

		// 清除所有标记
		clearMarkers() {
			// #ifdef H5
			if (this.candlestickSeries) {
				this.candlestickSeries.setMarkers([]);
				this.markers = [];
				this.orderMarkers = {}; // 清除订单计数器
				this.orderMarkerInfo = {}; // 清除订单标记信息
			}
			// #endif
		},

		// 清除所有价格线
		clearPriceLines() {
			// #ifdef H5
			if (this.candlestickSeries && this.priceLines.length > 0) {
				this.priceLines.forEach(line => {
					this.candlestickSeries.removePriceLine(line);
				});
				this.priceLines = [];
			}
			// #endif
		},

		// 清除所有标记和价格线
		clearAll() {
			this.clearMarkers();
			this.clearPriceLines();
		},

		// 处理窗口大小变化
		handleResize() {
			// #ifdef H5
			if (this.chart) {
				const container = document.getElementById(this.containerId);
				if (container) {
					const chartH = this.chartHeightPx || container.clientHeight || 300;
					this.chart.applyOptions({
						width: container.clientWidth,
						height: chartH
					});
				}
			}
			// #endif
		},

		// 销毁图表
		destroyChart() {
			// #ifdef H5
			console.log('[destroyChart] 销毁图表和 WebSocket 连接');

			// 清除重连定时器
			if (this.reconnectTimer) {
				clearTimeout(this.reconnectTimer);
				this.reconnectTimer = null;
			}

			// 关闭 K线 WebSocket（移除所有事件监听器）
			if (this.ws) {
				console.log('[destroyChart] 关闭 K线 WebSocket');
				this.ws.onopen = null;
				this.ws.onmessage = null;
				this.ws.onerror = null;
				this.ws.onclose = null;
				this.ws.close();
				this.ws = null;
			}

			if (this.chart) {
				window.removeEventListener('resize', this.handleResize);
				this.chart.remove();
				this.chart = null;
			}

			this.candlestickSeries = null;
			this.historicalData = [];
			this.markers = [];
			this.priceLines = [];
			this.orderMarkers = {};
			this.orderPriceLines = {};
			this.orderMarkerInfo = {};
			// #endif
		},

		// 更新交易对
		updateSymbol(newSymbol) {
			console.log('[updateSymbol] 切换交易对:', this.symbol, '→', newSymbol);
			this.destroyChart();
			this.$nextTick(() => {
				this.initChart();
			});
		},

		// 更新时间周期
		updateInterval(newInterval) {
			// #ifdef H5
			console.log('[updateInterval] 切换周期:', this.interval, '→', newInterval);

			// 设置标志，防止自动重连
			this.isChangingInterval = true;

			// 关闭现有 K线 WebSocket（注意：priceWs 不需要关闭，因为价格流与周期无关）
			if (this.ws) {
				this.ws.onopen = null;
				this.ws.onmessage = null;
				this.ws.onerror = null;
				this.ws.onclose = null;
				this.ws.close();
				this.ws = null;
				console.log('[updateInterval] 已关闭旧的 K线 WebSocket');
			}

			// 清除 K线 ws 重连定时器
			if (this.reconnectTimer) {
				clearTimeout(this.reconnectTimer);
				this.reconnectTimer = null;
			}

			// 显示加载状态
			this.loading = true;

			// 清除订单标记计数器（周期变化后 markerKey 会不同，需要重置）
			this.orderMarkers = {};

			// 重新计算所有现有订单的 markerKey
			this.recalculateOrderMarkers();

			// 只重新加载 K线数据和连接 K线 WebSocket，priceWs 保持不变
			this.fetchHistoricalData().then(() => {
				this.connectWebSocket();
			});
			// #endif
		},

		// 重新计算所有订单标记（周期切换后调用）
		recalculateOrderMarkers() {
			// #ifdef H5
			const intervalSeconds = this.getIntervalSeconds();
			const newOrderMarkers = {};
			const newMarkers = [];

			// 遍历所有订单标记信息，重新计算 key
			Object.keys(this.orderMarkerInfo).forEach(orderId => {
				const info = this.orderMarkerInfo[orderId];
				// 使用原始订单时间戳，重新对齐到新周期
				const originalTime = info.originalTime || info.time;
				// 关键修复：需要加上 timezoneOffset 后再对齐（与 addBuyUpMarker/addBuyDownMarker 保持一致）
				const adjustedTime = originalTime + this.timezoneOffset;
				const newTime = Math.floor(adjustedTime / intervalSeconds) * intervalSeconds;
				const newKey = `${newTime}_${info.type}`;

				// 更新订单标记信息
				this.orderMarkerInfo[orderId] = {
					...info,
					time: newTime,
					key: newKey,
					originalTime: originalTime // 保留原始时间戳
				};

				// 更新计数器
				if (!newOrderMarkers[newKey]) {
					newOrderMarkers[newKey] = 0;
				}
				newOrderMarkers[newKey]++;
			});

			this.orderMarkers = newOrderMarkers;

			// 重建标记数组
			const markerGroups = {};
			Object.keys(this.orderMarkerInfo).forEach(orderId => {
				const info = this.orderMarkerInfo[orderId];
				const key = info.key;
				if (!markerGroups[key]) {
					markerGroups[key] = {
						time: info.time,
						type: info.type,
						count: 0
					};
				}
				markerGroups[key].count++;
			});

			// 生成新的标记数组
			Object.values(markerGroups).forEach(group => {
				const isUp = group.type === 'up';
				const prefix = isUp ? 'U' : 'D';
				const text = group.count > 1 ? `${prefix}*${group.count}` : prefix;

				newMarkers.push({
					time: group.time,
					position: isUp ? 'aboveBar' : 'belowBar',
					color: isUp ? '#26a69a' : '#ef5350',
					shape: 'text',
					text: text,
					size: 1
				});
			});

			// 按时间排序
			newMarkers.sort((a, b) => a.time - b.time);
			this.markers = newMarkers;

			// 更新图表标记
			if (this.candlestickSeries) {
				this.candlestickSeries.setMarkers(this.markers);
			}
			// #endif
		},

		// 获取当前价格
		getCurrentPrice() {
			return this.latestPrice;
		},

		// 获取历史数据
		getHistoricalData() {
			return this.historicalData;
		}
	},
	watch: {
		symbol(newVal, oldVal) {
			if (newVal !== oldVal) {
				this.updateSymbol(newVal);
			}
		},
		interval(newVal, oldVal) {
			if (newVal !== oldVal) {
				this.updateInterval(newVal);
			}
		}
	}
}
</script>

<style scoped>
.lightweight-chart-container {
	position: relative;
	width: 100%;
	height: 100%;
}

.chart-widget {
	width: 100%;
	height: 100%;
}

.loading {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	display: flex;
	align-items: center;
	justify-content: center;
	background-color: rgba(0, 0, 0, 0.7);
	z-index: 10;
}

.loading-text {
	font-size: 24rpx;
	color: #999999;
}

.chart-fallback {
	width: 100%;
	display: flex;
	align-items: center;
	justify-content: center;
	background-color: #2C2C2C;
	border-radius: 8rpx;
}

.fallback-text {
	font-size: 28rpx;
	color: #999999;
}

/* 时区容器 */
.timezone-wrapper {
	position: absolute;
	bottom: 8rpx;
	right: 12rpx;
	z-index: 100;
}

/* 时区标签（可点击） */
.timezone-label {
	display: flex;
	align-items: center;
	gap: 6rpx;
	background-color: rgba(0, 0, 0, 0.6);
	padding: 6rpx 12rpx;
	border-radius: 6rpx;
	cursor: pointer;
	transition: background-color 0.2s;
}

.timezone-label:active {
	background-color: rgba(0, 0, 0, 0.8);
}

.timezone-text {
	font-size: 20rpx;
	color: #999999;
	font-family: monospace;
}

.timezone-arrow {
	font-size: 16rpx;
	color: #999999;
}

/* 时区下拉框 */
.timezone-dropdown {
	position: absolute;
	bottom: 100%;
	right: 0;
	margin-bottom: 8rpx;
	min-width: 350rpx;
	max-height: 500rpx;
	background-color: #1A1A1A;
	border: 1px solid #2C2C2C;
	border-radius: 12rpx;
	box-shadow: 0 -8rpx 24rpx rgba(0, 0, 0, 0.4);
	overflow: hidden;
}

.timezone-dropdown-list {
	max-height: 400rpx;
}

.timezone-dropdown-item {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 16rpx 20rpx;
	transition: background-color 0.2s;
}

.timezone-dropdown-item:active {
	background-color: #2C2C2C;
}

.timezone-dropdown-item.timezone-item-active {
	background-color: rgba(74, 135, 249, 0.3);
}

.timezone-dropdown-text {
	font-size: 22rpx;
	color: #FFFFFF;
	font-weight: 400;
	font-family: monospace;
}

.timezone-item-active .timezone-dropdown-text {
	color: #FFFFFF;
	font-weight: 500;
}

.timezone-check-icon {
	font-size: 24rpx;
	color: #4A87F9;
	margin-left: 10rpx;
}

/* 时区下拉框遮罩 */
.timezone-overlay {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	z-index: 99;
	background-color: transparent;
}
</style>
