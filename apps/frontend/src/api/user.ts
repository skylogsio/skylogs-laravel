"use server";

import type{ ServerResponse } from "@/@types/global";
import axios from "@/lib/axios";

const USER_URL = "user";
const USER_PASSWORD_URL = "user/pass";

export async function createUser(body: unknown) {
  return axios.post<ServerResponse<unknown>>(USER_URL, body).then((response) => response.data);
}

export async function updateUser(userId: unknown, body: unknown) {
  return axios
    .put<ServerResponse<unknown>>(`${USER_URL}/${userId}`, body)
    .then((response) => response.data);
}

export async function deleteUser(userId: unknown) {
  return axios
    .delete<ServerResponse<unknown>>(`${USER_URL}/${userId}`)
    .then((response) => response.data);
}

export async function changePassword(userId: unknown, body: unknown) {
  return axios
    .put<ServerResponse<unknown>>(`${USER_PASSWORD_URL}/${userId}`, body)
    .then((response) => response.data);
}
