"use server";

import type { ServerResponse } from "@/@types/global";
import axios from "@/lib/axios";

const DATA_SOURCE_URL = "data-source";

export async function createDataSource(body: unknown) {
  return axios
    .post<ServerResponse<unknown>>(DATA_SOURCE_URL, body)
    .then((response) => response.data);
}

export async function updateDataSource(dataSourceId: unknown, body: unknown) {
  return axios
    .put<ServerResponse<unknown>>(`${DATA_SOURCE_URL}/${dataSourceId}`, body)
    .then((response) => response.data);
}

export async function deleteDataSource(dataSourceId: unknown) {
  return axios
    .delete<ServerResponse<unknown>>(`${DATA_SOURCE_URL}/${dataSourceId}`)
    .then((response) => response.data);
}
