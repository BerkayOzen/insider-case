import { type AxiosRequestConfig } from 'axios';
import axiosInstance from '../plugins/axios';

type Config = AxiosRequestConfig & { skip?: boolean };
const axios = axiosInstance;

const apiBaseUrl =
  (import.meta as ImportMeta & { env?: { VITE_API_BASE_URL?: string } }).env?.VITE_API_BASE_URL ||
  '/api';

export default class Query {
  protected base: string;
  public endpoint: string;
  public queries: string[];
  protected config: Config = { skip: false };

  constructor(baseUrl: string = apiBaseUrl, endpoint: string = '/') {
    this.base = baseUrl;
    this.endpoint = endpoint;
    this.queries = [];
  }
  get resource() {
    return `${this.base}${this.endpoint}`;
  }

  query() {
    const query = '?' + this.queries.join('&');
    this.queries = [];
    return query;
  }

  parameter(key: string, value: string) {
    if (value) {
      this.queries.push(`${key}${value ? '=' + value : ''}`);
    }
    return this;
  }

  sort(field: string, sortBy = 'asc') {
    if (field) {
      this.queries.push(`orderBy=${field}`);
      this.queries.push(`sortedBy=${sortBy}`);
    }
    return this;
  }

  paginate(page = 1, perPage = 10) {
    //this.queries.push(`paginate=${perPage}`);
    this.queries.push(`pageSize=${perPage}`);
    this.queries.push(`page=${page}`);
    return this;
  }

  async all() {
    return await axios.get(`${this.resource}${this.query()}`, this.config);
  }

  async show(id: string) {
    return await axios.get(
      `${this.resource}/${id}${this.query()}`,
      this.config,
    );
  }

  async store(params: object | null) {
    return await axios.post(`${this.resource}${this.query()}`, params);
  }

  async update(id: string, params: object) {
    return await axios.put(`${this.resource}/${id}${this.query()}`, params);
  }

  async destroy(id: string) {
    return await axios.delete(`${this.resource}/${id}${this.query()}`);
  }

  async remove() {
    return await axios.delete(`${this.resource}${this.query()}`, this.config);
  }
}
