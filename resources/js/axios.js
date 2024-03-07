import axios from 'axios'

 const http = axios.create({
  baseURL: 'http://localhost:8000/api', // replace with the base URL of your API
  timeout: 10000, // indicates, 10000ms before the request times out
  allowCredentials: true,
 })
http.defaults.headers.common['Access-Control-Allow-Origin'] = '*';

export default http
