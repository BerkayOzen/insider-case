import axios, { type AxiosInstance } from 'axios';

const axiosInstance: AxiosInstance = axios.create({
  baseURL: '/',
  headers: {
    'Content-Type': 'application/json;charset=utf-8',
  },
});


export default axiosInstance;
