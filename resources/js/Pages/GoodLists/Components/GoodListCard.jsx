import { useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import DeletegoodListConfirmModal from '@/Pages/GoodLists/Components/Modals/DeleteGoodListConfirmModal';
import { usePage, router } from '@inertiajs/react';

export default function GoodListCard({ shopId, goodList, selectedGoodsId, setSelectedGoodsId }) {
    const { auth } = usePage().props;
    const [modalState, setModalIState] = useState({
        changeSettingModalIsOpen: false,
        deleteConfirmModalIsOpen: false,
    });

    const handleAddGoodsToListButtonClick = (e) => {
        e.preventDefault();
        router.patch(route('shops.goodlists.update', { shop: shopId, goodlist: goodList.id }), {
            selectedGoodsId: selectedGoodsId,
            type: 'add',
        }, {
            preserveScroll: true,
            onSuccess: () => {
                setSelectedGoodsId([]);
                window.location.reload();
            },
        });
    };

    const openDeleteModal = (() => {
        setModalIState({ deleteConfirmModalIsOpen: true });
    });

    const closeDeleteModal = (() => {
        setModalIState({ deleteConfirmModalIsOpen: false });
    });

    const userIsCreator = goodList.creator.id === auth.user.id;

    return (
        <div className="border border-gray-300 rounded-md shadow-sm bg-white mb-2 p-2">
            <p>ID: {goodList.id}</p>
            <p>{goodList.name}</p>
            <p>Создатель: {goodList.creator.name}</p>
            <div className="flex flex-col">
                <div className="flex gap-x-2">
                    <PrimaryButton
                        className="mt-4 max-w-fit"
                        onClick={(e) => router.get(route('shops.goodlists.show', [shopId, goodList.id]))}>
                        {userIsCreator ? 'Редактировать' : 'Посмотреть'}
                    </PrimaryButton>
                    {(goodList.creator.id === auth.user.id) &&
                        <>
                            <PrimaryButton
                                className="mt-4 max-w-fit"
                                onClick={(e) => openDeleteModal()}>
                                Удалить список
                            </PrimaryButton>
                            <PrimaryButton
                                disabled={selectedGoodsId.length === 0}
                                className="mt-4 max-w-fit"
                                onClick={(e) => handleAddGoodsToListButtonClick(e)}>
                                Добавить выбранные товары
                            </PrimaryButton>
                        </>
                    }


                </div>
                <DeletegoodListConfirmModal
                    shopId={shopId}
                    goodList={goodList}
                    maxWidth={'xl'}
                    IsOpen={modalState.deleteConfirmModalIsOpen}
                    closeModal={closeDeleteModal} />
            </div>
        </div >
    );
}
