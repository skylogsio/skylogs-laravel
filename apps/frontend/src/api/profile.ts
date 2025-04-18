"use server";

import type { IUser } from "@/@types/user";
import axios from "@/lib/axios";

export async function getMyInfo() {
  return axios.post<IUser>("auth/me").then((response) => response.data);
}
