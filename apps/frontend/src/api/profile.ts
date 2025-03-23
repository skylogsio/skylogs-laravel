import axios from "@/lib/axios";

export function getMyInfo() {
  return axios.post("auth/me");
}
