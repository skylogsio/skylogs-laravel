"use server";

import { FetchTableDataArgs, IServerResponseTabularDate } from "@/components/Table/types";
import axios from "@/lib/axios";

export async function fetchTableData<T>({
  url,
  pageSize,
  pageIndex,
  filterSearchParams
}: FetchTableDataArgs) {
  return axios<IServerResponseTabularDate<T>>(
    `${process.env.NEXT_PUBLIC_BASE_URL}${url}?perPage=${pageSize}&page=${pageIndex}&sortBy=_id&sortType=asc&${filterSearchParams}`
  ).then((response) => response.data);
}
