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




export interface FetchTableDataArgs {
  url: string;
  pageSize: number;
  pageIndex: number;
  filterSearchParams: string;
}
