export type TimeUnit = "s" | "m" | "h";

export interface IFlowStep {
  type: "wait" | "endpoint";
  duration?: number;
  timeUnit?: TimeUnit;
  endpointIds?: string[];
}

export interface IFlow {
  id: string;
  user_id: string;
  name: string;
  type: "flow";
  steps: IFlowStep[];
  isPublic: boolean;
  updated_at: Date;
  created_at: Date;
}

export interface ICreateFlowRequest {
  name: string;
  type: "flow";
  steps: IFlowStep[];
  isPublic: boolean;
}
