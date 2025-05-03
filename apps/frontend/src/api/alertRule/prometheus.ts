"use server";

import axios from "@/lib/axios";

const ALERT_RULE_PROMETHEUS_URL = "prometheus";

export async function getPrometheusAlertRuleName(): Promise<string[]> {
  try {
    const response = await axios.get<string[]>(`${ALERT_RULE_PROMETHEUS_URL}/rules`);
    return response.data;
  } catch (error) {
    throw error;
  }
}
