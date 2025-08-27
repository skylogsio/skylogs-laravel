export type StateType = "resolved" | "warning" | "critical";

export interface IStatusCard {
  id: string;
  name: string;
  state: StateType;
  criticalCount: number;
  warningCount: number;
  createdAt: string;
  updatedAt: string;
}
