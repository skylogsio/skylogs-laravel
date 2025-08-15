import type { IEndpoint } from "@/@types/endpoint";
import type { IUser } from "@/@types/user";
import { type AlertRuleType } from "@/utils/alertRuleUtils";

export interface IAlertRuleCreateData {
  endpoints: IEndpoint[];
  users: IUser[];
}

export type AlertRuleStatus = "resolved" | "warning" | "critical" | "triggered" | "unknown";

export interface IAlertRule {
  apiToken?: string;
  name: string;
  type: AlertRuleType;
  user_id: string;
  enableAutoResolve: boolean;
  autoResolveMinutes: number;
  updated_at: Date;
  created_at: Date;
  endpoint_ids: string[];
  user_ids: string[];
  id: string;
  ownerName: string;
  hasAdminAccess: boolean;
  status_label: AlertRuleStatus;
  is_silent: boolean;
  isPinned: boolean;
  count_endpoints: number;
  tags: string[];
  dataSourceAlertName?: string;
  dataSourceIds?: string[];
  dataSourceLabels?: string[];
}

export interface IAlertRuleEndpoints {
  alertEndpoints: Array<IEndpoint>;
  selectableEndpoints: Array<IEndpoint>;
}

export interface IAlertRuleUsers {
  alertUsers: Array<IUser>;
  selectableUsers: Array<IUser>;
}

export interface IApiAndNotificationAlertRuleHistory {
  alertRuleId: string;
  alertRuleName: string;
  instance: string;
  description: string;
  summary: string;
  state: number;
  status: AlertRuleStatus;
  updatedAt: string;
  createdAt: string;
  id: string;
}

export interface IApiAlertRuleInstance {
  alertRuleId: string;
  alertRuleName: string;
  instance: string;
  job: string;
  state: number;
  description: string;
  summary: string;
  updated_at: string;
  created_at: string;
  historyId: string;
  name: string | null;
  file: string;
  fileName: string;
  updatedAt: string;
  id: string;
  status: AlertRuleStatus;
}

export interface IPrometheusAlertRuleInstance {
  dataSourceId: string;
  dataSourceName: string;
  alertRuleName: string;
  dataSourceAlertName: string;
  labels: Record<string, string>;
  annotations: Record<string, string>;
  alertRuleId: string;
  skylogsStatus: number;
}

export interface IPrometheusAlertHistory {
  alertRuleId: string;
  alerts: IPrometheusAlertRuleInstance[];
  state: number;
  countResolve: number;
  countFire: number;
  updatedAt: string;
  createdAt: string;
  id: string;
}
