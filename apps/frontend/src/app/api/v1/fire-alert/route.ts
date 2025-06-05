import { NextRequest, NextResponse } from "next/server";

import { isAxiosError } from "axios";

import axios from "@/lib/axios";

export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const headers: Record<string, string> = {};
    req.headers.forEach((value, key) => {
      if (key !== "host" && key !== "content-length") {
        headers[key] = value;
      }
    });

    const response = await axios.post("fire-alert", body, { headers });
    return NextResponse.json(response.data, {
      status: response.status
    });
  } catch (error: unknown) {
    if (isAxiosError(error)) {
      console.error("Error forwarding request:", error.message);

      const status = error.response?.status || 500;
      const data = error.response?.data || { message: "Internal Server Error" };

      return NextResponse.json(data, {
        status
      });
    }
  }
}
