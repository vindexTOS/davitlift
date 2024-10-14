<template>
    <div class="notification-container">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        class="notification"
        @click="markAsRead(notification.id)"
      >
        <p>{{ notification.message }}</p>
        <small>{{ formatDate(notification.created_at) }}</small>
      </div>
  
      <div class="pagination">
        <button @click="prevPage" :disabled="currentPage === 1">Previous</button>
        <span>Page {{ currentPage }} of {{ totalPages }}</span>
        <button @click="nextPage" :disabled="currentPage === totalPages">Next</button>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      notifications: Array,
      totalPages: Number,
      currentPage: Number,
    },
    methods: {
      markAsRead(id) {
        // Handle marking notification as read
        this.$emit('mark-as-read', id);
      },
      formatDate(dateString) {
        return new Date(dateString).toLocaleString(); // Adjust formatting as needed
      },
      prevPage() {
        this.$emit('change-page', this.currentPage - 1);
      },
      nextPage() {
        this.$emit('change-page', this.currentPage + 1);
      },
    },
  };
  </script>
  
  <style>
  .notification-container {
    display: flex;
    flex-direction: column;
    padding: 10px;
    position: absolute;
  }
  .notification {
    border: 1px solid #ccc;
    border-radius: 5px;
    margin-bottom: 10px;
    padding: 10px;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  .notification:hover {
    background-color: #f5f5f5;
  }
  .pagination {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
  }
  </style>
  