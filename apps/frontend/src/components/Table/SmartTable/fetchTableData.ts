"use server";

import type { IServerResponseTabularData } from "@/@types/global";
import type { FetchTableDataArgs } from "@/components/Table/types";
import axios from "@/lib/axios";

export async function fetchTableData<T>({
  url,
  pageIndex,
  pageSize,
  filterSearchParams,
  searchKey,
  searchValue
}: FetchTableDataArgs): Promise<IServerResponseTabularData<T>> {
  try {
    const searchParams = new URLSearchParams({
      perPage: pageSize,
      page: pageIndex + 1,
      [searchKey]: searchValue
    } as Record<string, string>);
    const urlSearchParams = searchParams.toString();
    const endpoint = `${process.env.NEXT_PUBLIC_BASE_URL}${url}?${urlSearchParams}&sortBy=_id&sortType=asc&${filterSearchParams}`;
    console.log(endpoint);
    const response = await axios.get<IServerResponseTabularData<T>>(endpoint);
    // const response = await axios.get<IServerResponseTabularData<T>>(
    //   `${process.env.NEXT_PUBLIC_BASE_URL}${url}?perPage=${pageSize}&page=${pageIndex + 1}&${searchKey}=${searchValue}&sortBy=_id&sortType=asc&${filterSearchParams}`
    // );
    return response.data;
  } catch (error) {
    console.log(error);
    throw error;
  }
}
