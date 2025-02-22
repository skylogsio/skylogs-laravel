import axios from "@/utils/axios";

const USER_URL = "user";

export function createUser(body: unknown) {
  return axios.post(USER_URL, body);
}

export function updateUser(endpointId:unknown,body: unknown) {
  return axios.put(`${USER_URL}/${endpointId}`, body);
}

export function deleteUser(endpointId:unknown) {
  return axios.delete(`${USER_URL}/${endpointId}`);
}
