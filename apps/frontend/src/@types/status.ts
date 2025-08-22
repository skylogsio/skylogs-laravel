export type StateType = "resolve" | "warning" | "critical";

export interface IStatusCard {
  id: string;
  name: string;
  state: StateType;
  criticalCount: number;
  warningCount: number;
  createdAt: string;
  updatedAt: string;
}
