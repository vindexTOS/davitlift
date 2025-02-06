<template>
    <section class="blocked-cards-section">
      <v-container>
        <v-row>
          <v-col>
            <!-- Optional: You can add filtering inputs here if needed -->
            <v-btn class="blue-button" @click="getBlockedCardLogs">
              Get Blocked Card Logs
            </v-btn>
          </v-col>
        </v-row>
      </v-container>
  
      <div class="logs-wrapper">
        <div class="logs-container" @click.stop>
          <!-- Loop through the blocked card logs -->
          <div
            v-for="log in logsData"
            :key="log.id"
            class="log-entry"
          >
            <p>
              <strong>RFID:</strong> {{ log.rfid }}
            </p>
            <p>
              <strong>User ID:</strong> {{ log.user_id }}
            </p>
            <p>
              <strong>Device ID:</strong> {{ log.device_id }}
            </p>
            <p>
              <strong>Date:</strong> {{ formatDate(log.created_at) }}
            </p>
          </div>
          <!-- Pagination Controls -->
          <div class="pagination">
            <button @click="prevPage" :disabled="currentPage === 1">
              Previous
            </button>
            <span>Page {{ currentPage }} of {{ totalPages }}</span>
            <button @click="nextPage" :disabled="currentPage === totalPages">
              Next
            </button>
          </div>
        </div>
      </div>
    </section>
  </template>
  
  <script>
  import axios from "axios";
  
  export default {
    name: "BlockedCardLogs",
    data() {
      return {
        logsData: [],
        totalPages: 1,
        currentPage: 1,
      };
    },
    methods: {
      // Fetch blocked card logs for a given page (default page 1)
      getBlockedCardLogs(page = 1) {
        axios
          .get("/api/blocked-card-logs", { params: { page } })
          .then(({ data }) => {
            // Assuming your backend returns a paginated response with these keys:
            // - data.data: the array of log records
            // - data.last_page: total number of pages
            // - data.current_page: the current page number
            this.logsData = data.data;
            this.totalPages = data.last_page;
            this.currentPage = data.current_page;
          })
          .catch((error) => {
            console.error("Error fetching blocked card logs:", error);
          });
      },
      // Navigate to the next page
      nextPage() {
        if (this.currentPage < this.totalPages) {
          this.getBlockedCardLogs(this.currentPage + 1);
        }
      },
      // Navigate to the previous page
      prevPage() {
        if (this.currentPage > 1) {
          this.getBlockedCardLogs(this.currentPage - 1);
        }
      },
      // Format date for display
      formatDate(dateString) {
        const options = {
          year: "numeric",
          month: "long",
          day: "numeric",
          hour: "2-digit",
          minute: "2-digit",
          second: "2-digit",
        };
        return new Date(dateString).toLocaleString("en-US", options);
      },
    },
    mounted() {
      // Load the first page on component mount
      this.getBlockedCardLogs();
    },
  };
  </script>
  
  <style scoped>
  .blocked-cards-section {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    align-items: center;
    justify-content: center;
  }
  
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
  
  .logs-wrapper {
    margin-top: 20px;
    display: flex;
    justify-content: center;
    width: 100%;
  }
  
  .logs-container {
    background-color: #fff;
    width: 400px;
    height: 600px;
    overflow-y: auto;
    border-radius: 5px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    padding: 10px;
  }
  
  .log-entry {
    border: 1px solid #ccc;
    border-radius: 5px;
    margin-bottom: 10px;
    padding: 10px;
    transition: background-color 0.3s;
    cursor: pointer;
  }
  
  .log-entry:hover {
    background-color: #f5f5f5;
  }
  
  .pagination {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
  }
  
  .pagination button {
    padding: 5px 10px;
    background-color: #007bff;
    border: none;
    color: white;
    border-radius: 3px;
    cursor: pointer;
  }
  
  .pagination button:disabled {
    background-color: #cccccc;
    cursor: not-allowed;
  }
  </style>
  