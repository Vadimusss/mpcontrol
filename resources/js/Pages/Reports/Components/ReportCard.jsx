import React, { useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import { Inertia } from '@inertiajs/inertia';

export default function ReportCard({ auth, shopId, report, goodLists }) {
// console.log(workSpace);
    return (
        <div className="border border-gray-300 rounded-md shadow-sm bg-white mb-2 p-2">
            <p><span className='font-semibold text-gray-900'>Ворока продаж:</span> {workSpace.name}</p>
            <p><span className='font-semibold text-gray-900'>Создатель:</span> {workSpace.creator.name}</p>
            {workSpace.connected_good_lists.length !== 0 &&
                <>
                    <p><span className='font-semibold text-gray-900'>Подключенные списки:</span>
                    {workSpace.connected_good_lists.map((list) => <React.Fragment key={list.id}> {list.name}</React.Fragment> )}
                    </p>
                </>
            }
            <div className="flex flex-col">
                {(workSpace.creator.id === auth.user.id) &&
                    <>
                        <div className="flex gap-x-2">
                            <PrimaryButton
                                className="mt-4 max-w-fit"
                                onClick={(e) => openChangeSettingModal(e)}>
                                Настройки
                            </PrimaryButton>
                            <PrimaryButton
                                className="mt-4 max-w-fit"
                                onClick={(e) => openDeleteModal()}>
                                Удалить
                            </PrimaryButton>
                        </div>
                        <SetWorkSpaceSettingModal
                            shopId={shopId}
                            workSpace={workSpace}
                            goodLists={goodLists}
                            maxWidth={'xl'}
                            IsOpen={modalState.changeSettingModalIsOpen}
                            closeModal={closeChangeSettingModal}
                        />
                        <DeleteWorkSpaceConfirmModal
                            shopId={shopId}
                            workSpace={workSpace}
                            maxWidth={'xl'}
                            IsOpen={modalState.deleteConfirmModalIsOpen}
                            closeModal={closeDeleteModal} />
                    </>
                }
                <PrimaryButton
                    className="mt-4 max-w-fit"
                    onClick={(e) => Inertia.get(route('shops.workspaces.show', { shop: shopId, workspace: workSpace.id }))}>
                    Скачать
                </PrimaryButton>
            </div>
        </div >
    );
}
