import axios from "@/utils/axios";

const USER_URL = "user";
const USER_PASSWORD_URL = "user/pass";

export function createUser(body: unknown) {
  return axios.post(USER_URL, body);
}

export function updateUser(userId:unknown,body: unknown) {
  return axios.put(`${USER_URL}/${userId}`, body);
}

export function deleteUser(userId:unknown) {
  return axios.delete(`${USER_URL}/${userId}`);
}

export function changePassword(userId:unknown,body:unknown) {
  return axios.put(`${USER_PASSWORD_URL}/${userId}`, body);
}
