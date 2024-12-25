export default function SimpleTable({ goods }) {

    return (
        <table className="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead className="text-xs text-gray-700 bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" className="px-6 py-3">
                        nmID
                    </th>
                    <th scope="col" className="px-6 py-3">
                        vendorCode
                    </th>
                    <th scope="col" className="px-6 py-3">
                        currency
                    </th>
                    <th scope="col" className="px-6 py-3">
                        discount
                    </th>
                    <th scope="col" className="px-6 py-3">
                        clubDiscount
                    </th>
                    <th scope="col" className="px-6 py-3">
                        editableSizePrice
                    </th>

                </tr>
            </thead>
            <tbody>
                {goods && goods.map((good) =>
                    <>
                        <tr key={good.id} className="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" className="px-6 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {good.nm_id}
                            </th>
                            <td className="px-6 py-2">
                                {good.vendor_code}
                            </td>
                            <td className="px-6 py-2">
                                {good.wb_list_good_row.currency_iso_code_4217}
                            </td>
                            <td className="px-6 py-2">
                                {good.wb_list_good_row.discount}
                            </td>
                            <td className="px-6 py-2">
                                {good.wb_list_good_row.club_discount}
                            </td>
                            <td className="px-6 py-2">
                                {good.wb_list_good_row.editable_size_price}
                            </td>
                        </tr>
{/*                         <>
                            {good.sizes.length !== 0 && good.sizes.map(size)}
                        </> */}
                    </>
                )}
            </tbody>
        </table>
    );
}