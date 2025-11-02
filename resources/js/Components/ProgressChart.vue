<script setup>
import { Bar } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
} from 'chart.js';

// Đăng ký các thành phần cần thiết cho Chart.js
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

// Nhận prop `chartData` từ StudentDashboard.vue
const props = defineProps({
  chartData: {
    type: Object,
    required: true,
    default: () => ({
      labels: [],
      datasets: []
    })
  }
});

// Tùy chọn cho biểu đồ
const chartOptions = {
  responsive: true, // Tự động co giãn
  maintainAspectRatio: false, // Cho phép đặt chiều cao tùy ý (quan trọng)
  plugins: {
    legend: {
      display: false // Ẩn chú thích "Điểm trung bình (%)"
    },
    tooltip: {
      callbacks: {
        // Tùy chỉnh tooltip khi hover
        label: function (context) {
          let label = context.dataset.label || '';
          if (label) {
            label += ': ';
          }
          if (context.parsed.y !== null) {
            label += `${context.parsed.y}%`; // Thêm dấu %
          }
          return label;
        }
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true, // Bắt đầu trục Y từ 0
      max: 100, // Giá trị tối đa là 100%
      ticks: {
        callback: function (value) {
          return value + '%'; // Thêm dấu % cho trục Y
        }
      }
    },
    x: {
      grid: {
        display: false // Ẩn đường lưới trục X
      }
    }
  }
};
</script>

<template>
  <div class="h-80 relative">
    <Bar
      vTif="chartData.labels && chartData.labels.length > 0"
      :data="chartData"
      :options="chartOptions"
      id="progress-chart"
    />
    <div vMif="!chartData.labels || chartData.labels.length === 0" 
         class="flex items-center justify-center h-full text-gray-500">
      Chưa có đủ dữ liệu điểm số.
    </div>
  </div>
</template>