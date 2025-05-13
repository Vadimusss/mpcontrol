import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import SalesFunnelReportCard from '@/Pages/Reports/Components/SalesFunnelReportCard';
import StocksAndOrdersReportCard from '@/Pages/Reports/Components/StocksAndOrdersReportCard';
import { Head } from '@inertiajs/react';

export default function Report({ shop, reports, goodLists }) {
  const handleDownload = (shopId, report, beginDate, endDate, goodListId = null) => {

    const params = new URLSearchParams({
      'shopId': shopId,
      'reportId': report.id,
      'beginDate': beginDate,
      'endDate': endDate,
      'goodListId': goodListId,
    });

    const fileName = goodListId ?
      `${report.type.name} ${goodLists.filter((goodList) => goodList.id === Number(goodListId))[0].name} ${beginDate} - ${endDate}.xlsx` :
      `${report.type.name} ${beginDate} - ${endDate}.xlsx`;

    fetch('/reports/export?' + params.toString(), {
      method: 'GET',
      headers: {
        'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      },
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error('Ошибка при скачивании файла');
        }
        return response.blob();
      })
      .then((blob) => {
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', fileName);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      })
      .catch((error) => {
        console.error('Ошибка при скачивании файла:', error);
      });
  };

  return (
    <AuthenticatedLayout
      navigation={true}
      shopId={shop.id}
      header={
        <h2 className="text-xl font-semibold leading-tight text-gray-800">
          {shop.name}
        </h2>
      }>
      <Head title={`Магазин ${shop.name}`} />
      <div className="max-w-2xl mx-auto">
        <div className="p-2 sm:p-3 lg:p-6">
          <h2 className="text-xl font-bold mb-3">Выгрузки</h2>
          {reports.map((report) => report.type.id === 1 ?
            <SalesFunnelReportCard
              goodLists={goodLists}
              shopId={shop.id}
              report={report}
              handleDownload={handleDownload}
              key={report.id}
            /> :
            <StocksAndOrdersReportCard
              shopId={shop.id}
              report={report}
              handleDownload={handleDownload}
              key={report.id}
            />
          )}
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
