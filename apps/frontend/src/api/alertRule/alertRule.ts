"use server";

import type {
  IAlertRuleCreateData,
  IAlertRuleEndpoints,
  IAlertRuleUsers
} from "@/@types/alertRule";
import type { IEndpoint } from "@/@types/endpoint";
import type { ServerResponse, ServerSelectableDataType } from "@/@types/global";
import axios from "@/lib/axios";
import { DataSourceType } from "@/utils/dataSourceUtils";

const ALERT_RULE_URL = "alert-rule";
const ALERT_RULE_NOTIFY_URL = "alert-rule-notify";
const ALERT_RULE_USER_URL = "alert-rule-user";
const ALERT_RULE_TAGS_URL = "alert-rule-tag";
const ALERT_RULE_CREATE_DATA_URL = `${ALERT_RULE_URL}/create-data`;

export async function createAlertRule(body: unknown): Promise<ServerResponse<unknown>> {
  try {
    const response = await axios.post<ServerResponse<unknown>>(ALERT_RULE_URL, body);
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function updateAlertRule(
  alertRuleId: string,
  body: unknown
): Promise<ServerResponse<unknown>> {
  try {
    const response = await axios.put<ServerResponse<unknown>>(
      `${ALERT_RULE_URL}/${alertRuleId}`,
      body
    );
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function deleteAlertRule(alertRuleId: string): Promise<ServerResponse<unknown>> {
  try {
    const response = await axios.delete<ServerResponse<unknown>>(
      `${ALERT_RULE_URL}/${alertRuleId}`
    );
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function testAlertRule(id: string): Promise<ServerResponse<unknown>> {
  try {
    const response = await axios.post<ServerResponse<unknown>>(
      `${ALERT_RULE_NOTIFY_URL}/test/${id}`
    );
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function silenceAlertRule(id: string): Promise<ServerResponse<unknown>> {
  try {
    const response = await axios.post<ServerResponse<unknown>>(`${ALERT_RULE_URL}/silent/${id}`);
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function getAlertFilterEndpointList(): Promise<IEndpoint[]> {
  try {
    const response = await axios.get<Array<IEndpoint>>(`${ALERT_RULE_URL}/filter-endpoints`);
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function resolveFiredAlertRule(alertRuleId: string): Promise<ServerResponse<unknown>> {
  try {
    const response = await axios.post<ServerResponse<unknown>>(
      `${ALERT_RULE_URL}/resolve/${alertRuleId}`
    );
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function getAlertRuleEndpointsList(alertRuleId: string): Promise<IAlertRuleEndpoints> {
  try {
    const response = await axios.get<IAlertRuleEndpoints>(
      `${ALERT_RULE_NOTIFY_URL}/${alertRuleId}`
    );
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function addEndpointToAlertRule(
  alertRuleId: string,
  endpointIds: string[]
): Promise<ServerResponse<unknown>> {
  try {
    const response = await axios.put<ServerResponse<unknown>>(
      `${ALERT_RULE_NOTIFY_URL}/${alertRuleId}`,
      { endpoint_ids: endpointIds }
    );
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function removeEndpointFromAlertRule(
  alertRuleId: string,
  endpointId: string
): Promise<ServerResponse<unknown>> {
  try {
    const response = await axios.delete<ServerResponse<unknown>>(
      `${ALERT_RULE_NOTIFY_URL}/${alertRuleId}/${endpointId}`
    );
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function getAlertRuleUsersList(alertRuleId: string): Promise<IAlertRuleUsers> {
  try {
    const response = await axios.get<IAlertRuleUsers>(`${ALERT_RULE_USER_URL}/${alertRuleId}`);
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function addUsersToAlertRule(
  alertRuleId: string,
  userIds: string[]
): Promise<ServerResponse<unknown>> {
  try {
    const response = await axios.put<ServerResponse<unknown>>(
      `${ALERT_RULE_USER_URL}/${alertRuleId}`,
      { user_ids: userIds }
    );
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function removeUserFromAlertRule(
  alertRuleId: string,
  userId: string
): Promise<ServerResponse<unknown>> {
  try {
    const response = await axios.delete<ServerResponse<unknown>>(
      `${ALERT_RULE_USER_URL}/${alertRuleId}/${userId}`
    );
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function getAlertRuleTags(): Promise<string[]> {
  try {
    const response = await axios.get<string[]>(`${ALERT_RULE_TAGS_URL}`);
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function getAlertRuleLabels(): Promise<string[]> {
  try {
    const response = await axios.get<string[]>(`${ALERT_RULE_URL}/labels`);
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function getAlertRuleLabelValues(label: string): Promise<string[]> {
  try {
    const response = await axios.get<string[]>(`${ALERT_RULE_URL}/label-values/${label}`);
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function getAlertRuleCreateData(): Promise<IAlertRuleCreateData> {
  try {
    const response = await axios.get<IAlertRuleCreateData>(ALERT_RULE_CREATE_DATA_URL);
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function getAlertRuleDataSourcesByAlertType(
  type: DataSourceType
): Promise<ServerSelectableDataType> {
  try {
    const response = await axios.get<ServerSelectableDataType>(
      `${ALERT_RULE_CREATE_DATA_URL}/data-source/${type}`
    );
    return response.data;
  } catch (error) {
    throw error;
  }
}

export async function getDataSourceAlertName(type: DataSourceType) {
  try {
    const response = await axios.get(`${ALERT_RULE_CREATE_DATA_URL}/rules?type=${type}`);
    return response.data;
  } catch (error) {
    throw error;
  }
}
