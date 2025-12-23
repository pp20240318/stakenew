<template>
	<!-- #ifdef H5 -->
	<view class="tv-chart-container">
		<view v-if="loading" class="loading">
			<text class="loading-text">{{ $t('加载图表中...') }}</text>
		</view>
		<view :id="containerId" class="chart-widget"></view>
	</view>
	<!-- #endif -->
	
	<!-- #ifndef H5 -->
	<view class="tv-chart-fallback">
		<text class="fallback-text">{{ $t('TradingView 图表仅在 H5 端可用') }}</text>
	</view>
	<!-- #endif -->
</template>

<script>
export default {
	name: 'TradingViewChart',
	props: {
		symbol: {
			type: String,
			default: 'BTCUSDT'
		},
		interval: {
			type: String,
			default: 'D'
		},
		theme: {
			type: String,
			default: 'dark'
		},
		autosize: {
			type: Boolean,
			default: true
		},
		height: {
			type: Number,
			default: 500
		}
	},
	data() {
		return {
			containerId: `tv_chart_container_${Date.now()}`,
			tvWidget: null,
			loading: true
		}
	},
	mounted() {
		// #ifdef H5
		// 延迟初始化，确保 DOM 已渲染
		this.$nextTick(() => {
			setTimeout(() => {
				this.initTradingView();
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
		initTradingView() {
			// 检查是否在 H5 环境
			// #ifdef H5
			// 检查 TradingView 库是否已加载
			if (typeof window.TradingView === 'undefined' || !window.TradingView.widget) {
				console.error('TradingView library not loaded');
				this.loading = false;
				this.$emit('error', 'TradingView library not loaded');
				return;
			}

			try {
				const widgetOptions = {
					symbol: this.symbol,
					interval: this.interval,
					container: this.containerId,
					library_path: '/static/charting_library/',
					locale: 'zh',
					disabled_features: [
						'use_localstorage_for_settings',
						'header_symbol_search',
						'header_compare'
					],
					enabled_features: ['study_templates'],
					fullscreen: false,
					autosize: this.autosize,
					theme: this.theme,
					style: '1', // 蜡烛图
					timezone: 'Asia/Shanghai',
					toolbar_bg: '#2C2C2C',
					overrides: {
						'mainSeriesProperties.candleStyle.upColor': '#34C759',
						'mainSeriesProperties.candleStyle.downColor': '#FF3B30',
						'mainSeriesProperties.candleStyle.borderUpColor': '#34C759',
						'mainSeriesProperties.candleStyle.borderDownColor': '#FF3B30',
						'mainSeriesProperties.candleStyle.wickUpColor': '#34C759',
						'mainSeriesProperties.candleStyle.wickDownColor': '#FF3B30',
						'paneProperties.background': '#1A1A1A',
						'paneProperties.vertGridProperties.color': '#2C2C2C',
						'paneProperties.horzGridProperties.color': '#2C2C2C'
					},
					studies_overrides: {},
					datafeed: new window.Datafeeds.UDFCompatibleDatafeed('https://demo_feed.tradingview.com')
				};

				this.tvWidget = new window.TradingView.widget(widgetOptions);

				this.tvWidget.onChartReady(() => {
					this.loading = false;
					this.$emit('ready', this.tvWidget);
					console.log('TradingView chart ready');
				});
			} catch (error) {
				console.error('Error initializing TradingView:', error);
				this.loading = false;
				this.$emit('error', error);
			}
			// #endif
		},
		
		// 销毁图表
		destroyChart() {
			// #ifdef H5
			if (this.tvWidget !== null) {
				try {
					this.tvWidget.remove();
				} catch (error) {
					console.error('Error destroying chart:', error);
				}
				this.tvWidget = null;
			}
			// #endif
		},
		
		// 更新交易对
		updateSymbol(newSymbol) {
			// #ifdef H5
			if (this.tvWidget && this.tvWidget.chart) {
				this.tvWidget.chart().setSymbol(newSymbol);
			}
			// #endif
		},
		
		// 更新时间周期
		updateInterval(newInterval) {
			// #ifdef H5
			if (this.tvWidget && this.tvWidget.chart) {
				this.tvWidget.chart().setResolution(newInterval);
			}
			// #endif
		}
	},
	watch: {
		symbol(newSymbol) {
			this.updateSymbol(newSymbol);
		},
		interval(newInterval) {
			this.updateInterval(newInterval);
		}
	}
}
</script>

<style scoped>
.tv-chart-container {
	position: relative;
	width: 100%;
	height: 100%;
	min-height: 500px;
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

.tv-chart-fallback {
	width: 100%;
	height: 500px;
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
</style>
