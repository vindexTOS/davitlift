<template>
    <section style="display: flex; flex-direction: column; height: 100vh; width: 100vw; align-content: center;justify-content: center; ">
      <v-container>
        <v-row>
          <v-col>
            <v-input>
              <template v-slot:prepend>
                <input v-model="userId" placeholder="user ID" />
              </template>
            </v-input>
            <v-btn class="blue-button" @click="getNotifications">
              Get notifications
            </v-btn>
          </v-col>
        </v-row>
      </v-container>
  
      <div class="notification-wrapper" @click="checkOutsideClick">
        <div class="notification-container2" @click.stop>
          <div v-for="notification in notificationData" :key="notification.id" class="notification">
            <p>{{ notification.message.slice(0, 50) }}</p>
            <p>{{ formatDate(notification.created_at) }}</p>
          </div>
          <div class="pagination">
            <button @click="prevPage" :disabled="currentPage === 1">Previous</button>
            <span>Page {{ currentPage }} of {{ totalPages }}</span>
            <button @click="nextPage" :disabled="currentPage === totalPages">Next</button>
          </div>
        </div>
      </div>
    </section>
  </template>
  
  <script>
  export default {
    data() {
      return {
        userId: '',
        notificationData: [],
        totalPages: 1,
        currentPage: 1,
      };
    },
    methods: {
      getNotifications(page = 1) {
        axios.get(`api/notifications/${this.userId}?page=${page}`).then(({ data }) => {
          this.notificationData = data.data;
          this.totalPages = data.last_page; 
          this.currentPage = data.current_page;  
        });
      },
      nextPage() {
        if (this.currentPage < this.totalPages) {
          this.getNotifications(this.currentPage + 1);
        }
      },
      prevPage() {
        if (this.currentPage > 1) {
          this.getNotifications(this.currentPage - 1);
        }
      },
      formatDate(dateString) {
        const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        return new Date(dateString).toLocaleString('en-US', options);
      },
    }
  };
  </script>
  
  <style scoped>
  .blue-button {
    background-color: blue;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
  }
  
  .blue-button:hover {
    background-color: darkblue;
  }
  
  input {
    height: 50px;
    background-color: rgb(202, 202, 202);
    border-radius: 5px;
  }
  
 
  
  .notification-container2{
    display: flex;
    flex-direction: column;  
    align-items: center;
    justify-content: flex-start; /* Ensure items stack from the top */
    background-color: rgb(255, 255, 255); /* White background */
    width: 400px;
 height: 600px; /* Prevent overflow */
    overflow-y: auto; /* Enable scrolling if content exceeds max-height */
    border-radius: 5px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3); /* Add shadow */
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
  