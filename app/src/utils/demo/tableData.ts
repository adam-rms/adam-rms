interface ITableData{
  name: string
  client: string
  amount: number
  status: "success" | "danger" | "warning" | "neutral" | "primary" | undefined
  dateStart: string
  dateEnd: string
};


const tableData: ITableData[] =  [
  {
    name: 'Showcase',
    client: 'CHMS',
    amount: 989.4,
    status: 'primary',
    dateStart: 'Feb 03 2020 04:13:15',
    dateEnd: 'Feb 07 2020 04:13:15'
  },
  {
    name: 'Romeo & Juliet',
    client: 'Shakesoc',
    amount: 12.50,
    status: 'primary',
    dateStart: 'July 12 2021 04:13:15',
    dateEnd: 'July 16 2021 04:13:15'
  },
  {
    name: 'Training',
    client: 'Internal',
    amount: 0.0,
    status: 'primary',
    dateStart: 'Oct 03 2021 04:13:15',
    dateEnd: 'Oct 07 2021 04:13:15'
  },
  
];

export default tableData;
export type {
  ITableData
};