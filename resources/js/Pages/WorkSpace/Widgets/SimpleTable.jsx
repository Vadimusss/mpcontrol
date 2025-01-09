import { useState, Fragment } from 'react';

export default function SimpleTable({ goods }) {
    const [openSizesState, setOpenSizesState] = useState(
        goods.reduce((acc, good) => {
            acc[good.id] = false;
            return acc;
        }, {})
    );

    const handleOnClick = (id) => {
        setOpenSizesState({ ...openSizesState, [id]: !openSizesState[id] });
    };

    console.log(openSizesState);

    return (
        <table className="w-full text-sm text-left text-gray-500 shadow-lg mb-10 bg-white">
            <thead className="text-sm bg-gray-200 text-gray-700">
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
                    <th scope="col" className="px-6 py-3">
                        sizes
                    </th>
                </tr>
            </thead>
            <tbody>
                {goods && goods.map((good) =>
                    <Fragment key={good.id}>
                        <tr className="bg-white border-b border-gray-300 text-base">
                            <th scope="row" className="px-6 py-2">
                                {good.nm_id}
                            </th>
                            <th scope="row" className="px-6 py-2">
                                {good.vendor_code}
                            </th>
                            <th scope="row" className="px-6 py-2">
                                {good.wb_list_good_row.currency_iso_code_4217}
                            </th>
                            <th scope="row" className="px-6 py-2">
                                {good.wb_list_good_row.discount}
                            </th>
                            <th scope="row" className="px-6 py-2">
                                {good.wb_list_good_row.club_discount}
                            </th>
                            <th scope="row" className="px-6 py-2">
                                {good.wb_list_good_row.editable_size_price}
                            </th>
                            <th scope="row" className="px-6 py-2">
                                <button
                                    className="border border-gray-300 rounded-md w-6 cursor-pointer hover:bg-gray-100"
                                    onClick={() => handleOnClick(good.id)}>{openSizesState[good.id] ? '-' : '+'}</button>
                            </th>
                        </tr>
                        {good.sizes.length !== 0 &&
                            <tr className={`border-b border-gray-500${openSizesState[good.id] ? '' : ' hidden'}`}>
                                <td colSpan={6} >
                                    <table className="w-full text-sm text-left text-gray-700">
                                        <thead className="">
                                            <tr>
                                                <th scope="col" className="px-6 py-1">
                                                    sizeID
                                                </th>
                                                <th scope="col" className="px-6 py-1">
                                                    price
                                                </th>
                                                <th scope="col" className="px-6 py-1">
                                                    discountedPrice
                                                </th>
                                                <th scope="col" className="px-6 py-1">
                                                    clubDiscountedPrice
                                                </th>
                                                <th scope="col" className="px-6 py-1">
                                                    techSizeName
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {good.sizes.map((size) =>
                                                <tr key={size.id}>
                                                    <th scope="row" className="px-6">
                                                        {size.size_id}
                                                    </th>
                                                    <td className="px-6">
                                                        {size.price}
                                                    </td>
                                                    <td className="px-6">
                                                        {size.discounted_price}
                                                    </td>
                                                    <td className="px-6">
                                                        {size.club_discounted_price}
                                                    </td>
                                                    <td className="px-6">
                                                        {size.tech_size_name}
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        }
                    </ Fragment>
                )}
            </tbody>
        </table>
    );
}