import axios from "@/lib/axios";

const DATA_SOURCE_URL = "data-source";

export function createDataSource(body: unknown) {
  return axios.post(DATA_SOURCE_URL, body);
}

export function updateDataSource(dataSourceId:unknown,body: unknown) {
  return axios.put(`${DATA_SOURCE_URL}/${dataSourceId}`, body);
}

export function deleteDataSource(dataSourceId:unknown) {
  return axios.delete(`${DATA_SOURCE_URL}/${dataSourceId}`);
}
