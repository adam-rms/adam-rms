import React, { useState, useEffect } from 'react'

import InfoCard from '../components/Cards/InfoCard';
import PageTitle from '../components/Typography/PageTitle';
import response from '../utils/demo/tableData';
import { ITableData } from "../utils/demo/tableData";
import {
  TableBody,
  TableContainer,
  Table,
  TableHeader,
  TableCell,
  TableRow,
  TableFooter,
  Avatar,
  Badge,
  Pagination,
} from '@windmill/react-ui';


import Footer from "../components/Shared/Footer";
import { cart, cash, people, statsChart } from "ionicons/icons";

interface CardContent {
  title: string;
  value: string;
  icon: string;
}

const cardContents: CardContent[] = [
  {
    title: "Clients",
    value: "35",
    icon: people,
  },
  {
    title: "Outstanding Payments",
    value: "£457.54",
    icon: cash,
  },
  {
    title: "Current Projects",
    value: "4",
    icon: cart,
  },
  {
    title: "Maintenance Jobs",
    value: "9",
    icon: statsChart,
  },
]

function Dashboard() {
  const [page, setPage] = useState(1);
  const [data, setData] = useState<ITableData[]>([]);

  // pagination setup
  const resultsPerPage = 10;
  const totalResults = response.length;

  // pagination change control
  function onPageChange(p: number) {
    setPage(p)
  };

  // on page change, load new sliced data
  // here you would make another server request for new data
  useEffect(() => {
    setData(response.slice((page - 1) * resultsPerPage, page * resultsPerPage))
  }, [page]);

  return (
    <>
      <PageTitle>Dashboard</PageTitle>

      {/* <!-- Cards --> */}
      <div className="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
        {cardContents.map((cardContent)=>{
          return (
            <InfoCard title={cardContent.title} value={cardContent.value} icon={cardContent.icon} />
          )
        })}
      </div>
      <div className="mb-8">
        <TableContainer>
          <Table>
            <TableHeader>
              <tr>
                <TableCell>Project Name</TableCell>
                <TableCell>Dates</TableCell>
                <TableCell>Status</TableCell>
                <TableCell>Amount Due</TableCell>
              </tr>
            </TableHeader>
            <TableBody>
              {data.map((user, i) => (
                <TableRow key={i}>
                  <TableCell>
                    <div className="flex items-center text-sm">
                      <div>
                        <p className="font-semibold">{user.name}</p>
                        <p className="text-xs text-gray-600 dark:text-gray-400">{user.client}</p>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <span className="text-sm">{new Date(user.dateStart).toLocaleDateString()} - {new Date(user.dateEnd).toLocaleDateString()}</span>
                  </TableCell>
                  <TableCell>
                    <Badge type={user.status}>{user.status}</Badge>
                  </TableCell>
                  <TableCell>
                    <span className="text-sm">£ {user.amount}</span>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
          <TableFooter>
            <Pagination
              totalResults={totalResults}
              resultsPerPage={resultsPerPage}
              label="Table navigation"
              onChange={onPageChange}
            />
          </TableFooter>
        </TableContainer>
      </div>
    </>
  );
};

export default Dashboard;
