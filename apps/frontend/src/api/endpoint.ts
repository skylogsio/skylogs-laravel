"use server";

import type { ServerResponse } from "@/@types/global";
import axios from "@/lib/axios";

const ENDPOINT_URL = "endpoint";

export async function createEndpoint(body: unknown) {
  return axios.post<ServerResponse<unknown>>(ENDPOINT_URL, body).then((response) => response.data);
}

export async function updateEndpoint(endpointId: unknown, body: unknown) {
  return axios
    .put<ServerResponse<unknown>>(`${ENDPOINT_URL}/${endpointId}`, body)
    .then((response) => response.data);
}

export async function deleteEndpoint(endpointId: unknown) {
  return axios
    .delete<ServerResponse<unknown>>(`${ENDPOINT_URL}/${endpointId}`)
    .then((response) => response.data);
}
