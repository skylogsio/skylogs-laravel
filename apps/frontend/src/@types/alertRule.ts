import type { IEndpoint } from "@/@types/endpoint";
import type { IUser } from "@/@types/user";

export interface IAlertRuleCreateData {
  prometheusDataSources: string[];
  grafanaDataSources: string[];
  splunkDataSources: string[];
  endpoints: IEndpoint[];
  users: IUser[];
}
