import axios from "@/utils/axios";

const ENDPOINT_URL = "endpoint";

export function createEndpoint(body: unknown) {
  return axios.post(ENDPOINT_URL, body);
}

export function updateEndpoint(endpointId:unknown,body: unknown) {
  return axios.put(`${ENDPOINT_URL}/${endpointId}`, body);
}

export function deleteEndpoint(endpointId:unknown) {
  return axios.delete(`${ENDPOINT_URL}/${endpointId}`);
}
