import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ReportCard from '@/Pages/Reports/Components/ReportCard';
import { Head } from '@inertiajs/react';

export default function Report({ shop, reports, goodLists }) {
        const handleDownload = (shopId, report, goodListId, beginDate, endDate) => {

            const params = new URLSearchParams({
                'shopId': shopId,
                'reportId': report.id,
                'goodListId': goodListId,
                'beginDate': beginDate,
                'endDate': endDate,
            });

            const goodListName = goodLists.filter((goodList) => goodList.id === Number(goodListId))[0].name;
            const fileName = `${report.type.name} ${goodListName} ${beginDate} - ${endDate}.xlsx`;

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
                return response.blob(); // Получаем бинарные данные
              })
              .then((blob) => {
                // Создаем ссылку для скачивания
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', fileName); // Указываем имя файла
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link); // Удаляем ссылку после скачивания
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
                    {reports.map((report) =>
                        <ReportCard 
                            goodLists={goodLists}
                            shopId={shop.id}
                            report={report}
                            handleDownload={handleDownload}
                            key={report.id}
                        />)}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
