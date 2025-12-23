.index-container {
	padding-bottom: 30rpx;
	background-color: #1A1A1A;
}

/* 轮播图 */
.banner {
	height: 300rpx;
	width: 100%;
}

.banner-image {
	width: 100%;
	height: 100%;
}

/* 快捷菜单 */
.quick-menu {
	display: flex;
	justify-content: space-around;
	padding: 20rpx;
	background-color: #2C2C2C;
	margin-bottom: 20rpx;
}

.menu-item {
	display: flex;
	flex-direction: column;
	align-items: center;
}

.menu-icon {
	width: 80rpx;
	height: 80rpx;
	border-radius: 50%;
	margin-bottom: 10rpx;
	display: flex;
	align-items: center;
	justify-content: center;
}

.fa-icon {
	background-color: #007AFF;
	color: #FFFFFF;
}

.fa-icon-inner {
	font-size: 36rpx;
}

.ri-icon {
	background-color: #007AFF;
	color: #FFFFFF;
}

.ri-icon-inner {
	font-size: 36rpx;
}

.menu-text {
	font-size: 24rpx;
	color: #FFFFFF;
}

/* 市场概览 */
.market-overview {
	background-color: #2C2C2C;
	padding: 30rpx;
	margin-bottom: 20rpx;
}

.overview-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 20rpx;
}

.section-title {
	font-size: 32rpx;
	font-weight: bold;
	color: #FFFFFF;
}

.time-filter {
	display: flex;
}

.time-item {
	font-size: 24rpx;
	color: #999999;
	padding: 6rpx 16rpx;
	border-radius: 20rpx;
	margin-left: 10rpx;
}

.time-item.active {
	background-color: #007AFF;
	color: #FFFFFF;
}

.market-stats {
	display: flex;
	justify-content: space-between;
}

.stat-card {
	flex: 1;
	background-color: #3A3A3A;
	padding: 20rpx;
	border-radius: 12rpx;
	margin: 0 10rpx;
}

.stat-card:first-child {
	margin-left: 0;
}

.stat-card:last-child {
	margin-right: 0;
}

.stat-label {
	font-size: 24rpx;
	color: #999999;
	margin-bottom: 10rpx;
	display: block;
}

.stat-value {
	font-size: 32rpx;
	font-weight: bold;
	color: #FFFFFF;
	display: block;
	margin-bottom: 6rpx;
}

.stat-change {
	font-size: 22rpx;
	padding: 2rpx 8rpx;
	border-radius: 10rpx;
}

.increase {
	background-color: rgba(52, 199, 89, 0.1);
	color: #34C759;
}

.decrease {
	background-color: rgba(255, 59, 48, 0.1);
	color: #FF3B30;
}

/* 热门币种 */
.hot-coins {
	background-color: #2C2C2C;
	padding: 30rpx;
	margin-bottom: 20rpx;
}

.section-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 20rpx;
}

.view-all {
	font-size: 24rpx;
	color: #007AFF;
}

.coin-tabs {
	display: flex;
	margin-bottom: 20rpx;
	overflow-x: auto;
	white-space: nowrap;
}

.tab-item {
	font-size: 26rpx;
	color: #999999;
	padding: 10rpx 20rpx;
	margin-right: 20rpx;
	border-radius: 20rpx;
}

.tab-item.active {
	background-color: #007AFF;
	color: #FFFFFF;
}

.coin-list {
	display: flex;
	flex-direction: column;
}

.coin-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 20rpx 0;
	border-bottom: 1rpx solid #3A3A3A;
}

.coin-item:last-child {
	border-bottom: none;
}

.coin-info {
	display: flex;
	align-items: center;
	flex: 3;
}

.coin-icon {
	width: 60rpx;
	height: 60rpx;
	border-radius: 30rpx;
	margin-right: 15rpx;
}

.coin-details {
	display: flex;
	flex-direction: column;
}

.coin-name {
	font-size: 28rpx;
	color: #FFFFFF;
	font-weight: bold;
}

.coin-symbol {
	font-size: 24rpx;
	color: #999999;
}

.coin-chart {
	flex: 3;
	height: 60rpx;
	padding: 0 20rpx;
}

.chart-placeholder {
	height: 100%;
	background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.05) 50%, transparent);
	border-radius: 4rpx;
}

.up-chart {
	background: linear-gradient(90deg, transparent, rgba(52, 199, 89, 0.2) 50%, transparent);
}

.down-chart {
	background: linear-gradient(90deg, transparent, rgba(255, 59, 48, 0.2) 50%, transparent);
}

.coin-price {
	flex: 2;
	display: flex;
	flex-direction: column;
	align-items: flex-end;
}

.price-value {
	font-size: 28rpx;
	color: #FFFFFF;
	font-weight: bold;
	margin-bottom: 6rpx;
}

.price-change {
	font-size: 22rpx;
	padding: 2rpx 8rpx;
	border-radius: 10rpx;
}

/* 币种列表 */
.coins-section {
	background-color: #2C2C2C;
	padding: 30rpx;
}

.search-btn {
	width: 60rpx;
	height: 60rpx;
	background-color: #3A3A3A;
	border-radius: 30rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 28rpx;
	color: #999999;
}

.coin-filter {
	margin-top: 20rpx;
}

.sort-row {
	display: flex;
	border-bottom: 1rpx solid #3A3A3A;
	padding-bottom: 15rpx;
	margin-bottom: 10rpx;
}

.sort-item {
	flex: 1;
	display: flex;
	align-items: center;
}

.sort-item:nth-child(1) {
	flex: 1.5;
}

.sort-title {
	font-size: 26rpx;
	color: #999999;
	margin-right: 6rpx;
}

.sort-arrow {
	font-size: 22rpx;
	color: #999999;
}

.coin-table {
	display: flex;
	flex-direction: column;
}

.coin-row {
	display: flex;
	padding: 15rpx 0;
	border-bottom: 1rpx solid #3A3A3A;
	align-items: center;
}

.coin-row:last-child {
	border-bottom: none;
}

.coin-cell {
	flex: 1;
}

.name-cell {
	flex: 1.5;
	display: flex;
	align-items: center;
}

.small-icon {
	width: 40rpx;
	height: 40rpx;
	border-radius: 20rpx;
	margin-right: 10rpx;
}

.coin-name-group {
	display: flex;
	flex-direction: column;
}

.coin-full-name {
	font-size: 26rpx;
	color: #FFFFFF;
}

.coin-abbr {
	font-size: 22rpx;
	color: #999999;
}

.price-cell {
	text-align: center;
}

.current-price {
	font-size: 26rpx;
	font-weight: bold;
	color: #FFFFFF;
}

.change-cell {
	display: flex;
	justify-content: flex-end;
}

.change-value {
	font-size: 24rpx;
	padding: 4rpx 10rpx;
	border-radius: 10rpx;
}

/* Loading状态 */
.loading-container {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: rgba(0, 0, 0, 0.8);
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	z-index: 9999;
}

.loading-icon {
	font-size: 60rpx;
	color: #007AFF;
	margin-bottom: 20rpx;
}

.loading-text {
	font-size: 28rpx;
	color: #999999;
}

/* API错误提示 */
.error-container {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: rgba(0, 0, 0, 0.8);
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	z-index: 9999;
	padding: 20rpx;
}

.error-icon {
	font-size: 60rpx;
	color: #FF3B30;
	margin-bottom: 20rpx;
}

.error-title {
	font-size: 28rpx;
	font-weight: bold;
	color: #FFFFFF;
	margin-bottom: 10rpx;
}

.error-text {
	font-size: 24rpx;
	color: #999999;
	margin-bottom: 20rpx;
	text-align: center;
}

.retry-button {
	background-color: #007AFF;
	color: #FFFFFF;
	padding: 10rpx 20rpx;
	border-radius: 10rpx;
	font-size: 26rpx;
} 