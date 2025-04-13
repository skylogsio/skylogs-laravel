import { type MouseEventHandler, ReactNode } from "react";

import { type ColumnDef } from "@tanstack/react-table";

export interface SearchBoxProps {
  title?: string;
}

export interface TableComponentRef {
  refreshData: () => void;
}

export interface TableFilterComponentProps {
  onChange: (key: string, value: unknown) => void;
}

export interface SmartTableComponentProps<T> extends SearchBoxProps {
  url: string;
  columns: ColumnDef<T>[];
  hasCheckbox?: boolean;
  defaultPage?: number;
  defaultPageSize?: number;
  rowsPerPageOptions?: Array<number>;
  onCreate?: MouseEventHandler<HTMLButtonElement> | undefined;
  refetchInterval?: number;
  filterComponent?: (props: TableFilterComponentProps) => ReactNode;
}

export interface DataTableComponentProps<T> {
  data:Array<T>;
  columns: ColumnDef<T>[];
  hasCheckbox?: boolean;
  isLoading?: boolean;
}

export interface IServerResponseTabularDate<T> {
  data: T[];
  current_page: number;
  last_page: number;
  from: number;
  first_page_url: string;
  last_page_url: string;
  next_page_url: string;
  path: "http://192.168.151.184:8000/api/v1/alert-rule";
  per_page: number;
  prev_page_url: string;
  to: number;
  total: number;
  links: Array<{
    url: string;
    label: string;
    active: boolean;
  }>;
}


export interface FetchTableDataArgs {
  url: string;
  pageSize: number;
  pageIndex: number;
  filterSearchParams: string;
}
