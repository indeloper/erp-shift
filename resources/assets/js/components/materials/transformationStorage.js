export const transformationStages = Object.freeze({
    transformationTypesSelection: Symbol("transformationTypesSelection"),
    fillingMaterialsToTransform: Symbol("fillingMaterialsToTransform"),
    fillingMaterialsAfterTransform: Symbol("fillingMaterialsAfterTransform"),
    fillingMaterialsRemains: Symbol("fillingMaterialsRemains"),
    fillingMaterialsTechnologicalLosses: Symbol("fillingMaterialsTechnologicalLosses"),
});

export const rowTypes = Object.freeze({
    rowHeader: Symbol("rowHeader"),
    rowFooter: Symbol("rowFooter"),
    rowData: Symbol("fillingMaterialsTechnologicalLosses"),
});
export let currentTransformationStage = transformationStages.transformationTypesSelection;

export let transformationData = new DevExpress.data.DataSource({
    store: new DevExpress.data.ArrayStore({
        key: "id",
        data: []
    }),
    sort: ["sortIndex"]
})


export function updateValidationData(validationData) {
    let validatedData = transformationData.store().createQuery()
        .filter(["validationUid", "=", validationData.validationUid])
        .toArray();

    validatedData.forEach((material) => {
        let validationState = "validated";
        let validationResult;

        if (validationData.isValid) {
            validationResult = "valid"
        } else {
            validationResult = "invalid"
        }

        transformationData.store().update(material.id, {
            validationState: validationState,
            validationResult: validationResult,
            errorMessage: validationData.errorMessage
        })
            .done(() => {
                transformationData.reload();
            });
    })
}

export function validateMaterialList(validationUid) {
    function validateQuantity(material) {
        if (material.rowType === rowTypes.rowData) {
            if (!material.quantity) {
                return ({
                    severity: 1000,
                    codename: "null_quantity",
                    message: "Количество в единицах измерения не заполнено",
                    standard_name: material.standard_name
                })
            }
        }
    }

    function validateAmount(material) {
        if (material.rowType === rowTypes.rowData) {
            if (!material.amount) {
                return ({
                    severity: 1000,
                    codename: "null_amount",
                    message: "Количество в штуках не заполнено",
                    standard_name: material.standard_name
                })
            }
        }
    }

    function validateTotalRemains(material) {
        if (material.rowType !== rowTypes.rowData) {
            return;
        }

        let filterArray = [];
        filterArray.push(["rowType", "=", rowTypes.rowData]);
        filterArray.push("and");
        filterArray.push(["rowTransformationStage", "=", material.rowTransformationStage])
        filterArray.push("and");
        filterArray.push(["standard_id", "=", material.standard_id]);
        filterArray.push("and");
        filterArray.push(["initial_comment_id", "=", material.initial_comment_id]);

        switch (material.accounting_type) {
            case 2:
                filterArray.push("and");
                filterArray.push(["quantity", "=", material.quantity]);
                break;
            default:
        }

        let materialsToCalculateTotalAmount = transformationData.store().createQuery()
            .filter(filterArray)
            .toArray();

        let materialToTransferTotalAmount = 0;

        materialsToCalculateTotalAmount.forEach((materialToCalculateTotalAmount) => {
            switch (materialToCalculateTotalAmount.accounting_type) {
                case 2:
                    materialToTransferTotalAmount += materialToCalculateTotalAmount.amount;
                    break;
                default:
                    materialToTransferTotalAmount += Math.round(materialToCalculateTotalAmount.quantity * materialToCalculateTotalAmount.amount / 100) * 100;
            }
        })

        if (material.total_amount < materialToTransferTotalAmount) {
            return ({
                severity: 1000,
                codename: "amount_is_larger_than_total_amount",
                message: "На объекте недостаточно материала",
                standard_name: material.standard_name
            })
        }
    }

    function updateValidationResult(validationData, validationResult, validationFunction) {
        validationData.forEach((material) => {
            let validationResponse = validationFunction(material);
            if (validationResponse) {
                validationResult.validationInfo.push(validationResponse);
            }
        })
    }

    let validationData;

    if (validationUid) {
        validationData = transformationData.store().createQuery()
            .filter(['validationUid', '=', validationUid])
            .toArray();
    } else {
        validationData = transformationData.store().createQuery()
            .toArray();
    }

    let validationResult = {validationUid: validationUid, validationInfo: []};

    console.log('validationData', validationData);


    if (validationData.rowTransformationStage === transformationStages.fillingMaterialsToTransform || validationData.rowTransformationStage === transformationStages.fillingMaterialsAfterTransform) {
        updateValidationResult(validationData, validationResult, validateQuantity);
        updateValidationResult(validationData, validationResult, validateAmount);
    }

    updateValidationResult(validationData, validationResult, validateTotalRemains);

    validationResult.isValid = validationResult.validationInfo.length === 0;

    if (!validationResult.isValid) {
        let validationErrors = [];

        validationResult.validationInfo.forEach((validationError) => {
            validationErrors.push(validationError.message);
        })

        validationResult.errorMessage = (Array.from(new Set(validationErrors))).join("<br>");
    }

    console.log("validationResult", validationResult);

    updateValidationData(validationResult);
}

export function validateStages() {
    function getStageFooterValidationUid(transformationStage) {
        let footerData = transformationData.store().createQuery()
            .filter([["rowTransformationStage", "=", transformationStage],
                "and",
                ["rowType", "=", rowTypes.rowFooter]])
            .toArray();

        if (footerData.length > 0) {
            return footerData[0].validationUid;
        }
    }

    function validateMaterialToTransformStage() {
        let footerValidationUid = getStageFooterValidationUid(transformationStages.fillingMaterialsToTransform);

        if (!footerValidationUid) {
            return;
        }

        let validationResult = {validationUid: footerValidationUid, validationInfo: []};

        let summary = calculateMaterialSummariesByStage(transformationStages.fillingMaterialsToTransform, "brands");

        let materialsToTransformMaterials = getMaterialsByStage(transformationStages.fillingMaterialsToTransform);

        let isAnyOfMaterialInvalid = false;

        materialsToTransformMaterials.forEach((material) => {
            if (material.validationResult === "invalid") {
                isAnyOfMaterialInvalid = true;
            }
        })

        if (isAnyOfMaterialInvalid) {
            validationResult.validationInfo.push({
                severity: 1000,
                codename: "some_materials_in_stage_invalid",
                message: `Данные у некоторых материалов введены некорректно`,
                //standard_name: material.standard_name
            })
        }

        validationResult.isValid = validationResult.validationInfo.length === 0;

        if (!validationResult.isValid) {
            let validationErrors = [];

            validationResult.validationInfo.forEach((validationError) => {
                validationErrors.push(validationError.message);
            })

            validationResult.errorMessage = (Array.from(new Set(validationErrors))).join("<br>");
        }

        updateValidationData(validationResult);

        updateSummaries(calculateMaterialSummariesByStage(transformationStages.fillingMaterialsToTransform, "brands"), transformationStages.fillingMaterialsToTransform);
    }

    function validateMaterialAfterTransformStage() {
        let footerValidationUid = getStageFooterValidationUid(transformationStages.fillingMaterialsAfterTransform);

        if (!footerValidationUid) {
            return;
        }

        let validationResult = {validationUid: footerValidationUid, validationInfo: []};

        let materialsToTransform = getMaterialsByStage(transformationStages.fillingMaterialsToTransform, "brands");
        let materialsAfterTransform = getMaterialsByStage(transformationStages.fillingMaterialsAfterTransform, "brands");
        console.log('@materialsToTransform', materialsToTransform)
        let brandsList = [];

        materialsToTransform.forEach((toSummaryBrand) => {
            let isBrandFound = false;

            materialsAfterTransform.forEach((afterSummaryBrand) => {
                if (toSummaryBrand.key === afterSummaryBrand.key) {
                    isBrandFound = true;
                }
            })

            if (!isBrandFound) {
                toSummaryBrand.items.forEach((item) => {
                    brandsList.push(item.standard_name);
                })


            }
        })

        console.log("brandsList", brandsList);

        if (brandsList.length) {
            validationResult.validationInfo.push({
                severity: 1000,
                codename: "some_brands_not_found",
                message: `Не все марки материалов добавлены в список [${Array.from(new Set(brandsList)).join(', ')}]`,
            })
        }

        let isAnyOfMaterialInvalid = false;

        materialsAfterTransform = getMaterialsByStage(transformationStages.fillingMaterialsAfterTransform);

        materialsAfterTransform.forEach((material) => {
            if (material.validationResult === "invalid") {
                isAnyOfMaterialInvalid = true;
            }
        })

        if (isAnyOfMaterialInvalid) {
            validationResult.validationInfo.push({
                severity: 1000,
                codename: "some_materials_in_stage_invalid",
                message: `Данные у некоторых материалов введены некорректно`,
                //standard_name: material.standard_name
            })
        }

        validationResult.isValid = validationResult.validationInfo.length === 0;

        if (!validationResult.isValid) {
            let validationErrors = [];

            validationResult.validationInfo.forEach((validationError) => {
                validationErrors.push(validationError.message);
            })

            validationResult.errorMessage = (Array.from(new Set(validationErrors))).join("<br>");
        }

        updateValidationData(validationResult);

        updateSummaries(calculateMaterialSummariesByStage(transformationStages.fillingMaterialsAfterTransform, "brands"), transformationStages.fillingMaterialsAfterTransform);
    }

    function validateFillingRemainsTransformStage() {
        let footerValidationUid = getStageFooterValidationUid(transformationStages.fillingMaterialsRemains);

        if (!footerValidationUid) {
            return;
        }

        let validationResult = {validationUid: footerValidationUid, validationInfo: []};

        let materialsToTransform = getMaterialsByStage(transformationStages.fillingMaterialsToTransform, "brands");
        let materialsAfterTransform = getMaterialsByStage(transformationStages.fillingMaterialsAfterTransform, "brands");
        let materialsRemains = getMaterialsByStage(transformationStages.fillingMaterialsRemains, "brands");
        let materialsTechnologicalLosses = getMaterialsByStage(transformationStages.fillingMaterialsTechnologicalLosses, "brands");

        checkTotalMaterialSummaryAfterTransformation();

        let isAnyOfMaterialInvalid = false;


        let materialsToTransformSummary = calculateMaterialSummariesByStage(transformationStages.fillingMaterialsToTransform, "brands");
        let materialsAfterTransformSummary = calculateMaterialSummariesByStage(transformationStages.fillingMaterialsAfterTransform, "brands");
        let materialsRemainsSummary = calculateMaterialSummariesByStage(transformationStages.fillingMaterialsRemains, "brands");
        let materialsTechnologicalLossesSummary = calculateMaterialSummariesByStage(transformationStages.fillingMaterialsTechnologicalLosses, "brands")


        console.log(`materialsToTransformSummary`, materialsToTransformSummary);
        console.log(`materialsRemainsSummary`, materialsRemainsSummary);

        let Total

        if (isAnyOfMaterialInvalid) {
            validationResult.validationInfo.push({
                severity: 1000,
                codename: "some_materials_in_stage_invalid",
                message: `Данные у некоторых материалов введены некорректно`,
                //standard_name: material.standard_name
            })
        }

        validationResult.isValid = validationResult.validationInfo.length === 0;

        if (!validationResult.isValid) {
            let validationErrors = [];

            validationResult.validationInfo.forEach((validationError) => {
                validationErrors.push(validationError.message);
            })

            validationResult.errorMessage = (Array.from(new Set(validationErrors))).join("<br>");
        }

        updateValidationData(validationResult);

        updateSummaries(calculateMaterialSummariesByStage(transformationStages.fillingMaterialsRemains, "brands"), transformationStages.fillingMaterialsRemains);
    }

    function validateTechnologicalLossesTransformStage() {
        let footerValidationUid = getStageFooterValidationUid(transformationStages.fillingMaterialsTechnologicalLosses);

        if (!footerValidationUid) {
            return;
        }

        let validationResult = {validationUid: footerValidationUid, validationInfo: []};

        let materialsToTransform = getMaterialsByStage(transformationStages.fillingMaterialsToTransform, "brands");
        let materialsAfterTransform = getMaterialsByStage(transformationStages.fillingMaterialsAfterTransform, "brands");
        let materialsRemains = getMaterialsByStage(transformationStages.fillingMaterialsRemains, "brands");
        let materialsTechnologicalLosses = getMaterialsByStage(transformationStages.fillingMaterialsTechnologicalLosses, "brands");

        let materialsTotalSummary = [];

        materialsAfterTransform.forEach((item) => {
            let isBrandFound = false;
            materialsTotalSummary.forEach((totalSummaryItem) => {
                if (totalSummaryItem.brand_id === item.brand_id) {
                    isBrandFound = true;
                    totalSummaryItem.quantity += item.quantity;
                    totalSummaryItem.weight += item.weight;
                }
            })

            if (!isBrandFound) {
                materialsTotalSummary.push({quantity: item.quantity, weight: item.weight});
            }
        })

        console.log(`totalSummaryItem`, materialsTotalSummary)

        let isAnyOfMaterialInvalid = false;

        materialsTechnologicalLosses = getMaterialsByStage(transformationStages.fillingMaterialsTechnologicalLosses);

        materialsTechnologicalLosses.forEach((material) => {
            if (material.validationResult === "invalid") {
                isAnyOfMaterialInvalid = true;
            }
        })

        if (isAnyOfMaterialInvalid) {
            validationResult.validationInfo.push({
                severity: 1000,
                codename: "some_materials_in_stage_invalid",
                message: `Данные у некоторых материалов введены некорректно`,
                //standard_name: material.standard_name
            })
        }

        validationResult.isValid = validationResult.validationInfo.length === 0;

        if (!validationResult.isValid) {
            let validationErrors = [];

            validationResult.validationInfo.forEach((validationError) => {
                validationErrors.push(validationError.message);
            })

            validationResult.errorMessage = (Array.from(new Set(validationErrors))).join("<br>");
        }

        updateValidationData(validationResult);

        updateSummaries(calculateMaterialSummariesByStage(transformationStages.fillingMaterialsTechnologicalLosses, "brands"), transformationStages.fillingMaterialsTechnologicalLosses);
    }

    function checkTotalMaterialSummaryAfterTransformation() {
        console.log(checkTotalMaterialSummaryAfterTransformation)
        let totalSummary = [];

        function addToTotalSummary(summaryArray) {
            summaryArray.forEach((itemAfterTransform) => {
                let isBrandFound = false;
                totalSummary.forEach((totalSummaryItem) => {
                    if (totalSummaryItem.brands === itemAfterTransform.brands) {
                        isBrandFound = true;
                        totalSummaryItem.quantity += itemAfterTransform.quantity;
                        totalSummaryItem.weight += itemAfterTransform.weight;
                    }
                })

                if (!isBrandFound) {
                    totalSummary.push({
                        quantity: itemAfterTransform.quantity,
                        weight: itemAfterTransform.weight,
                        brands: itemAfterTransform.brands
                    });
                }
            })
        }

        let materialsToTransform = calculateMaterialSummariesByStage(transformationStages.fillingMaterialsToTransform, "brands");
        let materialsAfterTransform = calculateMaterialSummariesByStage(transformationStages.fillingMaterialsAfterTransform, "brands");
        let materialsRemains = calculateMaterialSummariesByStage(transformationStages.fillingMaterialsRemains, "brands");
        let materialsTechnologicalLosses = calculateMaterialSummariesByStage(transformationStages.fillingMaterialsTechnologicalLosses, "brands");

        addToTotalSummary(materialsAfterTransform);
        addToTotalSummary(materialsRemains);
        addToTotalSummary(materialsTechnologicalLosses);

        materialsToTransform.forEach((item) => {
            totalSummary.forEach((totalSummaryItem) => {

            })
        })

        console.log(`addToTotalSummary`, totalSummary);


        return null;
    }

    function updateSummaries(summary, transformationStage) {
        let data = transformationData.store().createQuery()
            .filter([["rowTransformationStage", "=", transformationStage],
                "and",
                ["rowType", "=", rowTypes.rowFooter]])
            .toArray();

        if (data.length === 1) {
            let totalStageSummary = {quantity: 0, amount: 0, weight: 0};
            summary.forEach((item) => {
                totalStageSummary.quantity += item.quantity;
                totalStageSummary.amount += item.amount;
                totalStageSummary.weight += item.weight;
            })

            if (!totalStageSummary.quantity) {
                totalStageSummary.quantity = 0;
            } else {
                totalStageSummary.quantity = Math.round(totalStageSummary.quantity * 100) / 100;
            }

            if (!totalStageSummary.amount) {
                totalStageSummary.amount = 0;
            }

            if (!totalStageSummary.weight) {
                totalStageSummary.weight = 0;
            } else {
                totalStageSummary.weight = Math.round(totalStageSummary.weight * 1000) / 1000;
            }

            transformationData.store().update(
                data[0].id,
                {
                    quantity: totalStageSummary.quantity,
                    amount: totalStageSummary.amount,
                    weight: totalStageSummary.weight
                });
        }
    }

    function getMaterialsByStage(transformationStage, groupBy) {
        let filterArray = [["rowTransformationStage", "=", transformationStage],
            "and",
            ["rowType", "=", rowTypes.rowData]];

        let dataToCalculate = transformationData.store().createQuery()
            .filter(filterArray);

        if (groupBy) {
            dataToCalculate = dataToCalculate.groupBy(groupBy)
        }

        return dataToCalculate.toArray();
    }

    function calculateMaterialSummariesByStage(transformationStage, groupBy) {
        let dataToCalculate = getMaterialsByStage(transformationStage, groupBy);

        let summaryResult = [];

        dataToCalculate.forEach((data) => {
            let summaryStructure = {quantity: 0, amount: 0, weight: 0}
            data.items.forEach((item) => {
                if (item.quantity && item.amount) {
                    summaryStructure.quantity += item.quantity * item.amount;
                }

                if (item.amount) {
                    summaryStructure.amount += item.amount;
                }

                if (item.quantity && item.amount) {
                    summaryStructure.weight += item.quantity * item.amount * item.standard_weight;
                }

                if (groupBy) {
                    summaryStructure[groupBy] = item[groupBy];
                }
            })

            summaryResult.push(summaryStructure);
        })

        return summaryResult;
    }

    validateMaterialToTransformStage();
    validateMaterialAfterTransformStage();
    validateFillingRemainsTransformStage();
    validateTechnologicalLossesTransformStage();
}

export function insertTransformationRow(dataToInsert, transformationStage) {
    console.log('%c Добавление материалов в преобразование', 'color: green;')
    transformationData.store().insert(dataToInsert).done(() => {
        currentTransformationStage = transformationStage;
        transformationData.reload();

        if (dataToInsert.rowType === rowTypes.rowFooter) {
            validateStages(null);
        }

        if (dataToInsert.rowType === rowTypes.rowData) {
            updateRowFooter();
        }
    })
}

export function getValidationUid(material) {
    let filterConditions;

    switch (material.accounting_type) {
        case 2:
            if (!material.quantity || !material.amount) {
                return "uid-" + new DevExpress.data.Guid().toString();
            } else {
                filterConditions = [["standard_id", "=", material.standard_id],
                    "and",
                    ["quantity", "=", material.quantity],
                    "and",
                    ["amount", ">", 0],
                    "and",
                    ["initial_comment_id", "=", material.initial_comment_id],
                    "and",
                    ["rowType", "=", material.rowType]];
            }
            break;
        default:
            filterConditions = [["standard_id", "=", material.standard_id],
                "and",
                ["initial_comment_id", "=", material.initial_comment_id],
                "and",
                ["rowType", "=", material.rowType]];
    }

    let filteredData = transformationData.store().createQuery()
        .filter(filterConditions)
        .toArray();

    if (filteredData.length > 0) {
        return filteredData[0].validationUid
    } else {
        return "uid-" + new DevExpress.data.Guid().toString();
    }
}

function updateRowFooter() {
    console.log("updateRowFooter transformationData", transformationData.store().createQuery().toArray());

    let data = transformationData.store().createQuery()
        .filter([["rowType", "=", rowTypes.rowData],
            "and",
            ["rowTransformationStage", "=", currentTransformationStage]
        ])
        .toArray();

    let footerData = transformationData.store().createQuery()
        .filter([["rowType", "=", rowTypes.rowFooter],
            "and",
            ["rowTransformationStage", "=", currentTransformationStage]
        ])
        .toArray();

    let isFooterAlreadyInserted = footerData.length !== 0;

    if (data.length === 0) {
        deleteRowFooter()
    } else {
        if (!isFooterAlreadyInserted) {
            insertFooterRow();
        }
    }
}

function deleteRowFooter() {

}

function insertFooterRow() {
    let sortIndex = 0;
    switch (currentTransformationStage) {
        case transformationStages.fillingMaterialsToTransform:
            sortIndex = 3;
            break;
        case transformationStages.fillingMaterialsAfterTransform:
            sortIndex = 6;
            break;
        case transformationStages.fillingMaterialsRemains:
            sortIndex = 9;
            break;
        case transformationStages.fillingMaterialsTechnologicalLosses:
            sortIndex = 12;
            break;
    }

    let data = {
        id: "uid-" + new DevExpress.data.Guid().toString(),
        rowType: rowTypes.rowFooter,
        quantity: 0,
        amount: 0,
        weight: 0,
        measure_unit_value: 'м.п',
        rowTransformationStage: currentTransformationStage,
        sortIndex: sortIndex,
        validationUid: "uid-" + new DevExpress.data.Guid().toString(),
        validationState: "unvalidated",
        validationResult: "none",
        errorMessage: ""
    }

    insertTransformationRow(data, currentTransformationStage);

    console.log('transformationData array', transformationData.store().createQuery().toArray());
}
