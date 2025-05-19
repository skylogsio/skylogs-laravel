"use server";

import { FetchTableDataArgs, IServerResponseTabularDate } from "@/components/Table/types";
import axios from "@/lib/axios";

export async function fetchTableData<T>({
  url,
  pageSize,
  pageIndex,
  filterSearchParams
}: FetchTableDataArgs): Promise<IServerResponseTabularDate<T>> {
  try {
    const response = await axios.get<IServerResponseTabularDate<T>>(
      `${process.env.NEXT_PUBLIC_BASE_URL}${url}?perPage=${pageSize}&page=${pageIndex + 1}&sortBy=_id&sortType=asc&${filterSearchParams}`
    );
    return response.data;
  } catch (error) {
    throw error;
  }
}
